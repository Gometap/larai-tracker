<?php

namespace Gometap\LaraiTracker\Services;

class LaraiCostCalculator
{
    /**
     * Map of models to costs per 1,000,000 tokens in USD.
     * Format: [input_cost, output_cost]
     * Prices as of Feb 2024 (approximate).
     */
    /**
     * Calculate the cost of an AI call.
     */
    public function calculate(string $provider, string $model, int $promptTokens, int $completionTokens): float
    {
        $model = strtolower($model);
        $provider = strtolower($provider);
        
        $pricing = $this->getPricing($provider, $model);

        $inputCost = ($promptTokens / 1000000) * $pricing['input'];
        $outputCost = ($completionTokens / 1000000) * $pricing['output'];

        return round($inputCost + $outputCost, 8);
    }

    protected function getPricing(string $provider, string $model): array
    {
        // Try to get from database first
        try {
            $dbPrice = \Gometap\LaraiTracker\Models\LaraiModelPrice::where('provider', $provider)
                ->where('model', $model)
                ->first();

            if ($dbPrice) {
                return [
                    'input' => (float) $dbPrice->input_price_per_1m,
                    'output' => (float) $dbPrice->output_price_per_1m,
                ];
            }
        } catch (\Exception $e) {}

        // Fallback to defaults
        $defaults = [
            'openai' => [
                'gpt-4o' => ['input' => 5.00, 'output' => 15.00],
                'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
                'gpt-4' => ['input' => 30.00, 'output' => 60.00],
                'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50],
            ],
            'google' => [
                'gemini-1.5-pro' => ['input' => 3.50, 'output' => 10.50],
                'gemini-1.5-flash' => ['input' => 0.075, 'output' => 0.30],
                'gemini-pro' => ['input' => 0.50, 'output' => 1.50],
            ],
        ];

        return $defaults[$provider][$model] ?? ['input' => 10.00, 'output' => 30.00];
    }
}
