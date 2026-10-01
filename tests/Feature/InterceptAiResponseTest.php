<?php

use Gometap\LaraiTracker\Events\AiCallRecorded;
use Gometap\LaraiTracker\Listeners\InterceptAiResponse;
use Gometap\LaraiTracker\Tests\TestCase;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;

uses(TestCase::class);

function aiResponseEvent(string $url, array $payload, int $status = 200): ResponseReceived
{
    return new ResponseReceived(
        new Request(new PsrRequest('POST', $url)),
        new Response(new PsrResponse($status, ['Content-Type' => 'application/json'], json_encode($payload)))
    );
}

test('it records openai chat completion usage', function () {
    Event::fake([AiCallRecorded::class]);

    (new InterceptAiResponse)->handle(aiResponseEvent('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-4o-mini',
        'usage' => ['prompt_tokens' => 12, 'completion_tokens' => 7],
    ]));

    Event::assertDispatched(AiCallRecorded::class, fn ($event) => $event->provider === 'openai'
        && $event->model === 'gpt-4o-mini'
        && $event->promptTokens === 12
        && $event->completionTokens === 7
    );
});

test('it records openai responses api usage fields', function () {
    Event::fake([AiCallRecorded::class]);

    (new InterceptAiResponse)->handle(aiResponseEvent('https://api.openai.com/v1/responses', [
        'model' => 'gpt-4.1-mini',
        'usage' => ['input_tokens' => 8, 'output_tokens' => 3],
    ]));

    Event::assertDispatched(AiCallRecorded::class, fn ($event) => $event->promptTokens === 8 && $event->completionTokens === 3
    );
});

test('it records anthropic gemini azure and openrouter usage', function (string $url, array $payload, string $provider, string $model) {
    Event::fake([AiCallRecorded::class]);

    (new InterceptAiResponse)->handle(aiResponseEvent($url, $payload));

    Event::assertDispatched(AiCallRecorded::class, fn ($event) => $event->provider === $provider && $event->model === $model
    );
})->with([
    'anthropic' => [
        'https://api.anthropic.com/v1/messages',
        ['model' => 'claude-3-haiku-20240307', 'usage' => ['input_tokens' => 4, 'output_tokens' => 5]],
        'anthropic',
        'claude-3-haiku-20240307',
    ],
    'gemini' => [
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
        ['usageMetadata' => ['promptTokenCount' => 4, 'candidatesTokenCount' => 5]],
        'google',
        'gemini-2.0-flash',
    ],
    'azure' => [
        'https://example.openai.azure.com/openai/deployments/my-deployment/chat/completions',
        ['model' => 'gpt-4o', 'usage' => ['prompt_tokens' => 4, 'completion_tokens' => 5]],
        'azure',
        'gpt-4o',
    ],
    'openrouter' => [
        'https://openrouter.ai/api/v1/chat/completions',
        ['model' => 'openai/gpt-4o', 'usage' => ['prompt_tokens' => 4, 'completion_tokens' => 5]],
        'openrouter',
        'openai/gpt-4o',
    ],
]);

test('it ignores unrelated hosts even when they return a usage key', function () {
    Event::fake([AiCallRecorded::class]);

    (new InterceptAiResponse)->handle(aiResponseEvent('https://example.com/api', [
        'model' => 'not-an-ai-model',
        'usage' => ['prompt_tokens' => 100, 'completion_tokens' => 100],
    ]));

    Event::assertNotDispatched(AiCallRecorded::class);
});

test('it ignores failed responses and incomplete or invalid usage', function (array $payload, int $status) {
    Event::fake([AiCallRecorded::class]);

    (new InterceptAiResponse)->handle(aiResponseEvent('https://api.openai.com/v1/chat/completions', $payload, $status));

    Event::assertNotDispatched(AiCallRecorded::class);
})->with([
    'failed response' => [['usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1]], 429],
    'missing usage' => [['model' => 'gpt-4o'], 200],
    'negative usage' => [['usage' => ['prompt_tokens' => -1, 'completion_tokens' => 1]], 200],
    'non numeric usage' => [['usage' => ['prompt_tokens' => 'secret', 'completion_tokens' => 1]], 200],
    'integer overflow' => [['usage' => ['prompt_tokens' => '999999999999999999999', 'completion_tokens' => 1]], 200],
    'total overflow' => [['usage' => ['prompt_tokens' => 2147483647, 'completion_tokens' => 1]], 200],
]);
