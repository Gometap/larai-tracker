<?php

namespace Gometap\LaraiTracker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Gometap\LaraiTracker\LaraiTracker
 */
class Larai extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getStatic()
    {
        return 'larai-tracker';
    }
}
