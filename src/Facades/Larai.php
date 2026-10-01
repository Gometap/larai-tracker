<?php

namespace Gometap\LaraiTracker\Facades;

use Gometap\LaraiTracker\Services\LaraiCostCalculator;
use Illuminate\Support\Facades\Facade;

/**
 * @method static float|null calculate(string $provider, string $model, int $promptTokens, int $completionTokens)
 *
 * @see LaraiCostCalculator
 */
class Larai extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'larai-tracker';
    }
}
