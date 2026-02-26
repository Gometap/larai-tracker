<?php

namespace Gometap\LaraiTracker\Models;

use Illuminate\Database\Eloquent\Model;

class LaraiModelPrice extends Model
{
    protected $table = 'larai_model_prices';

    protected $fillable = [
        'provider',
        'model',
        'input_price_per_1m',
        'output_price_per_1m',
        'is_custom',
    ];

    protected $casts = [
        'input_price_per_1m' => 'decimal:4',
        'output_price_per_1m' => 'decimal:4',
        'is_custom' => 'boolean',
    ];
}
