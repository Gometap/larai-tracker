<?php

namespace Gometap\LaraiTracker\Services;

class LaraiCostCalculator
{
    /**
     * Map of models to costs per 1,000,000 tokens in USD.
     * Format: [input_cost, output_cost]
     * Prices as of Feb 2024 (approximate).
     */
    protected array $costs = [
        'gpt-4' => [30.00, 60.00],
        'gpt-4-turbo' => [10.00, 30.00],
        'gpt-4o' => [5.00, 15.00],
        'gpt-3.5-turbo' => [0.50, 1.50],
        'gemini-1.5-pro' => [3.50, 10.50],
        'gemini-1.5-flash' => [0.075, 0.30],
    ];

    /**
     * Calculate the cost of an AI call.
     */
    public function calculate(string $model, int $promptTokens, int $completionTokens): float
    {
        $model = strtolower($model);
        
        // Find best match for model name
        $pricing = $this->costs['gpt-3.5-turbo']; // Default
        foreach ($this->costs as $key => $value) {
            if (str_contains($model, $key)) {
                $pricing = $value;
                break;
            }
        }

        $inputCost = ($promptTokens / 1000000) * $pricing[0];
        $outputCost = ($completionTokens / 1000000) * $pricing[1];

        return round($inputCost + $outputCost, 8);
    }
}
