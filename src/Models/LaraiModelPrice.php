<?php

namespace Gometap\LaraiTracker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $provider
 * @property string $model
 * @property string $input_price_per_1m
 * @property string $output_price_per_1m
 * @property bool $is_custom
 * @property string|null $source_url
 * @property Carbon|null $effective_date
 * @property int|null $catalog_version
 */
class LaraiModelPrice extends Model
{
    protected $table = 'larai_model_prices';

    protected $fillable = [
        'provider',
        'model',
        'input_price_per_1m',
        'output_price_per_1m',
        'is_custom',
        'source_url',
        'effective_date',
        'catalog_version',
    ];

    protected $casts = [
        'input_price_per_1m' => 'decimal:4',
        'output_price_per_1m' => 'decimal:4',
        'is_custom' => 'boolean',
        'effective_date' => 'date',
        'catalog_version' => 'integer',
    ];
}
