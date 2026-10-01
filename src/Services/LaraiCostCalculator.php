<?php

namespace Gometap\LaraiTracker\Services;

use Gometap\LaraiTracker\Models\LaraiModelPrice;
use InvalidArgumentException;
use Throwable;

class LaraiCostCalculator
{
    protected static ?array $catalog = null;

    /**
     * Map of models to costs per 1,000,000 tokens in USD.
     * Format: [input_cost, output_cost]
     * Prices as of Feb 2024 (approximate).
     */
    /**
     * Calculate the cost of an AI call.
     */
    public function calculate(string $provider, string $model, int $promptTokens, int $completionTokens): ?float
    {
        if ($promptTokens < 0 || $completionTokens < 0) {
            throw new InvalidArgumentException('Token counts cannot be negative.');
        }

        $model = strtolower($model);
        $provider = strtolower($provider);

        $pricing = $this->getPricing($provider, $model);
        if ($pricing === null) {
            return null;
        }

        $inputCost = ($promptTokens / 1000000) * $pricing['input'];
        $outputCost = ($completionTokens / 1000000) * $pricing['output'];

        return round($inputCost + $outputCost, 8);
    }

    protected function getPricing(string $provider, string $model): ?array
    {
        // Try to get from database first
        try {
            $dbPrice = LaraiModelPrice::where('provider', $provider)
                ->where('model', $model)
                ->first();

            if ($dbPrice) {
                return [
                    'input' => (float) $dbPrice->input_price_per_1m,
                    'output' => (float) $dbPrice->output_price_per_1m,
                ];
            }
        } catch (Throwable) {
            // The package may be calculating before its migrations are installed.
        }

        return $this->packagedCatalog()[$provider][$model] ?? null;
    }

    protected function packagedCatalog(): array
    {
        if (self::$catalog !== null) {
            return self::$catalog;
        }

        self::$catalog = [];
        $path = __DIR__.'/../../resources/data/prices.json';
        $decoded = json_decode((string) @file_get_contents($path), true);
        $models = is_array($decoded) ? ($decoded['models'] ?? []) : [];

        foreach ($models as $item) {
            if (! is_array($item)
                || ! isset($item['provider'], $item['model'])
                || ! is_numeric($item['input_price_per_1m'] ?? null)
                || ! is_numeric($item['output_price_per_1m'] ?? null)
                || $item['input_price_per_1m'] < 0
                || $item['output_price_per_1m'] < 0) {
                continue;
            }

            self::$catalog[strtolower($item['provider'])][strtolower($item['model'])] = [
                'input' => (float) $item['input_price_per_1m'],
                'output' => (float) $item['output_price_per_1m'],
            ];
        }

        return self::$catalog;
    }
}
