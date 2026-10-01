<?php

namespace Gometap\LaraiTracker\Listeners;

use Gometap\LaraiTracker\Events\AiCallRecorded;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class InterceptAiResponse
{
    /**
     * Handle the event.
     */
    public function handle(ResponseReceived $event): void
    {
        try {
            if (! $event->response->successful()) {
                return;
            }

            $url = $event->request->url();
            $response = $event->response->json();

            if (! is_array($response)) {
                return;
            }

            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $path = (string) parse_url($url, PHP_URL_PATH);

            if ($host === 'api.anthropic.com' && $path === '/v1/messages') {
                $this->logAnthropicFormat($response);

                return;
            }

            if ($host === 'generativelanguage.googleapis.com' && str_contains($path, '/models/')) {
                $this->logGeminiFormat($url, $response);

                return;
            }

            if ($this->isAzureHost($host) && str_contains($path, '/openai/deployments/')) {
                $this->logOpenAiFormat($url, $response, 'azure');

                return;
            }

            if ($host === 'openrouter.ai' && $path === '/api/v1/chat/completions') {
                $this->logOpenAiFormat($url, $response, 'openrouter');

                return;
            }

            if ($host === 'api.openai.com' && in_array($path, ['/v1/chat/completions', '/v1/responses'], true)) {
                $this->logOpenAiFormat($url, $response, 'openai');
            }
        } catch (Throwable $exception) {
            Log::warning('Larai Tracker skipped an AI usage response.', [
                'exception' => $exception::class,
            ]);
        }
    }

    protected function isAzureHost(string $host): bool
    {
        return str_ends_with($host, '.openai.azure.com')
            || str_ends_with($host, '.services.ai.azure.com');
    }

    /**
     * Log usage in OpenAI-compatible format.
     */
    protected function logOpenAiFormat(string $url, array $response, string $provider): void
    {
        $usage = $response['usage'] ?? null;
        if (! is_array($usage)) {
            return;
        }

        $tokens = $this->tokens(
            $usage,
            ['prompt_tokens', 'input_tokens'],
            ['completion_tokens', 'output_tokens']
        );
        if ($tokens === null) {
            return;
        }

        $model = $response['model'] ?? null;
        if ((! is_string($model) || $model === '') && $provider === 'azure') {
            preg_match('#/deployments/([^/]+)#', (string) parse_url($url, PHP_URL_PATH), $matches);
            $model = isset($matches[1]) ? rawurldecode($matches[1]) : 'unknown';
        }

        $this->dispatch($provider, $model, $tokens);
    }

    /**
     * Log usage in Gemini format.
     * Gemini does not return the model name in the response body; extract it from the URL.
     * URL pattern: /v1beta/models/gemini-1.5-pro:generateContent
     */
    protected function logGeminiFormat(string $url, array $response): void
    {
        // Gemini returns usage in usageMetadata
        $usage = $response['usageMetadata'] ?? null;
        if (! is_array($usage)) {
            return;
        }

        $tokens = $this->tokens(
            $usage,
            ['promptTokenCount'],
            ['candidatesTokenCount', 'completionTokenCount']
        );
        if ($tokens === null) {
            return;
        }

        // Extract model from URL path, e.g. /models/gemini-1.5-pro:generateContent → gemini-1.5-pro
        $model = 'gemini-1.5-pro';
        if (preg_match('/\/models\/([^:\/]+)/i', $url, $matches)) {
            $model = $matches[1];
        }

        $this->dispatch('google', $model, $tokens);
    }

    /**
     * Log usage in Anthropic format.
     * Anthropic returns usage as { input_tokens, output_tokens } directly in response root.
     */
    protected function logAnthropicFormat(array $response): void
    {
        $usage = $response['usage'] ?? null;

        if (! is_array($usage)) {
            return;
        }

        $tokens = $this->tokens($usage, ['input_tokens'], ['output_tokens']);
        if ($tokens === null) {
            return;
        }

        $this->dispatch('anthropic', $response['model'] ?? null, $tokens);
    }

    /**
     * @param  array<string, mixed>  $usage
     * @param  array<int, string>  $inputKeys
     * @param  array<int, string>  $outputKeys
     * @return array{0: int, 1: int}|null
     */
    protected function tokens(array $usage, array $inputKeys, array $outputKeys): ?array
    {
        $input = $this->firstTokenValue($usage, $inputKeys);
        $output = $this->firstTokenValue($usage, $outputKeys);

        if (! $input['valid'] || ! $output['valid'] || (! $input['found'] && ! $output['found'])) {
            return null;
        }

        if ($input['value'] + $output['value'] > 2147483647) {
            return null;
        }

        return [$input['value'], $output['value']];
    }

    /**
     * @param  array<string, mixed>  $usage
     * @param  array<int, string>  $keys
     * @return array{found: bool, valid: bool, value: int}
     */
    protected function firstTokenValue(array $usage, array $keys): array
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $usage)) {
                continue;
            }

            $value = $usage[$key];
            if (! (is_int($value) || (is_string($value) && ctype_digit($value)))) {
                return ['found' => true, 'valid' => false, 'value' => 0];
            }

            $value = filter_var($value, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 2147483647],
            ]);

            return [
                'found' => true,
                'valid' => $value !== false,
                'value' => $value === false ? 0 : $value,
            ];
        }

        return ['found' => false, 'valid' => true, 'value' => 0];
    }

    protected function dispatch(string $provider, mixed $model, array $tokens): void
    {
        $model = is_string($model) && trim($model) !== '' ? trim($model) : 'unknown';

        AiCallRecorded::dispatch(
            Auth::id(),
            $provider,
            mb_substr($model, 0, 255),
            $tokens[0],
            $tokens[1]
        );
    }
}
