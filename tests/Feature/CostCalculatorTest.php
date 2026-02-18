<?php

use Gometap\LaraiTracker\Services\LaraiCostCalculator;

uses(\Gometap\LaraiTracker\Tests\TestCase::class);

test('it calculates GPT-4o input cost correctly', function () {
    $calculator = new LaraiCostCalculator();
    
    // GPT-4o: $5.00 per 1M input tokens
    // 100,000 tokens = $0.50
    $cost = $calculator->calculate('gpt-4o', 100000, 0);
    
    expect($cost)->toBe(0.50);
});

test('it calculates GPT-4o output cost correctly', function () {
    $calculator = new LaraiCostCalculator();
    
    // GPT-4o: $15.00 per 1M output tokens
    // 100,000 tokens = $1.50
    $cost = $calculator->calculate('gpt-4o', 0, 100000);
    
    expect($cost)->toBe(1.50);
});

test('it calculates Gemini Flash cost correctly', function () {
    $calculator = new LaraiCostCalculator();
    
    // Gemini Flash: $0.075 input, $0.30 output per 1M
    // 1M input + 1M output = $0.375
    $cost = $calculator->calculate('gemini-1.5-flash', 1000000, 1000000);
    
    expect($cost)->toBe(0.375);
});
