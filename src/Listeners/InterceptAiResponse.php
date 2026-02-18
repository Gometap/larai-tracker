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

        // OpenAI / Azure OpenAI / Groq / OpenRouter pattern
        if (str_contains($url, 'openai.com') || str_contains($url, 'openai.azure.com') || isset($response['usage'])) {
            $this->logOpenAiFormat($url, $response);
            return;
        }

        // Gemini pattern
        if (str_contains($url, 'generativelanguage.googleapis.com')) {
            $this->logGeminiFormat($response);
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
     */
    protected function logGeminiFormat(array $response): void
    {
        // Gemini returns usage in usageMetadata
        $usage = $response['usageMetadata'] ?? null;
        
        if (!$usage) {
            return;
        }

        AiCallRecorded::dispatch(
            Auth::id(),
            'gemini',
            $response['model'] ?? 'gemini-pro', // Gemini might not return model name in same place
            $usage['promptTokenCount'] ?? 0,
            $usage['candidatesTokenCount'] ?? $usage['completionTokenCount'] ?? 0
        );
    }
}
