<?php

use Gometap\LaraiTracker\Facades\Larai;
use Gometap\LaraiTracker\Services\LaraiCostCalculator;
use Gometap\LaraiTracker\Tests\TestCase;

uses(TestCase::class);

test('it calculates GPT-4o input cost correctly', function () {
    $calculator = new LaraiCostCalculator;

    // GPT-4o catalog snapshot: $2.50 per 1M input tokens
    // 100,000 tokens = $0.25
    $cost = $calculator->calculate('openai', 'gpt-4o', 100000, 0);

    expect($cost)->toBe(0.25);
});

test('it calculates GPT-4o output cost correctly', function () {
    $calculator = new LaraiCostCalculator;

    // GPT-4o catalog snapshot: $10.00 per 1M output tokens
    // 100,000 tokens = $1.00
    $cost = $calculator->calculate('openai', 'gpt-4o', 0, 100000);

    expect($cost)->toBe(1.0);
});

test('it calculates Gemini Flash cost correctly', function () {
    $calculator = new LaraiCostCalculator;

    // Gemini Flash: $0.075 input, $0.30 output per 1M
    // 1M input + 1M output = $0.375
    $cost = $calculator->calculate('google', 'gemini-1.5-flash', 1000000, 1000000);

    expect($cost)->toBe(0.375);
});

test('it returns unknown cost instead of inventing a fallback price', function () {
    $calculator = new LaraiCostCalculator;

    expect($calculator->calculate('unrelated', 'unknown-model', 1000, 1000))->toBeNull();
});

test('it rejects negative token counts', function () {
    $calculator = new LaraiCostCalculator;

    expect(fn () => $calculator->calculate('openai', 'gpt-4o', -1, 0))
        ->toThrow(InvalidArgumentException::class);
});

test('the public facade resolves the cost calculator', function () {
    expect(Larai::calculate('openai', 'gpt-4o-mini', 1000000, 0))->toBe(0.15);
});
