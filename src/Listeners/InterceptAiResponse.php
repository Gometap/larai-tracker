<?php

namespace Gometap\LaraiTracker\Listeners;

use Gometap\LaraiTracker\Events\AiCallRecorded;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Facades\Auth;

class InterceptAiResponse
{
    /**
     * Handle the event.
     */
    public function handle(ResponseReceived $event): void
    {
        $url = $event->request->url();
        $response = $event->response->json();

        if (!$response) {
            return;
        }

        // Anthropic pattern — must be checked before the generic `usage` catch-all
        if (str_contains($url, 'api.anthropic.com')) {
            $this->logAnthropicFormat($response);
            return;
        }

        // Gemini pattern
        if (str_contains($url, 'generativelanguage.googleapis.com')) {
            $this->logGeminiFormat($url, $response);
            return;
        }

        // OpenAI / Azure OpenAI / Groq / OpenRouter pattern
        if (str_contains($url, 'openai.com') || str_contains($url, 'openai.azure.com') || isset($response['usage'])) {
            $this->logOpenAiFormat($url, $response);
            return;
        }
    }

    /**
     * Log usage in OpenAI-compatible format.
     */
    protected function logOpenAiFormat(string $url, array $response): void
    {
        if (!isset($response['usage'])) {
            return;
        }

        $provider = 'openai';
        if (str_contains($url, 'azure.com')) {
            $provider = 'azure';
        } elseif (str_contains($url, 'openrouter.ai')) {
            $provider = 'openrouter';
        }

        AiCallRecorded::dispatch(
            Auth::id(),
            $provider,
            $response['model'] ?? 'unknown',
            $response['usage']['prompt_tokens'] ?? 0,
            $response['usage']['completion_tokens'] ?? 0
        );
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

        if (!$usage) {
            return;
        }

        // Extract model from URL path, e.g. /models/gemini-1.5-pro:generateContent → gemini-1.5-pro
        $model = 'gemini-1.5-pro';
        if (preg_match('/\/models\/([^:\/]+)/i', $url, $matches)) {
            $model = $matches[1];
        }

        AiCallRecorded::dispatch(
            Auth::id(),
            'google',
            $model,
            $usage['promptTokenCount'] ?? 0,
            $usage['candidatesTokenCount'] ?? $usage['completionTokenCount'] ?? 0
        );
    }

    /**
     * Log usage in Anthropic format.
     * Anthropic returns usage as { input_tokens, output_tokens } directly in response root.
     */
    protected function logAnthropicFormat(array $response): void
    {
        $usage = $response['usage'] ?? null;

        if (!$usage) {
            return;
        }

        AiCallRecorded::dispatch(
            Auth::id(),
            'anthropic',
            $response['model'] ?? 'unknown',
            $usage['input_tokens'] ?? 0,
            $usage['output_tokens'] ?? 0
        );
    }
}
