<?php

namespace Gometap\LaraiTracker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $amount
 * @property string $currency_code
 * @property int $alert_threshold
 * @property string|null $recipient_email
 * @property bool $is_active
 * @property Carbon|null $last_alerted_at
 * @property string|null $last_alert_period
 * @property int|null $last_alert_threshold
 */
class LaraiBudget extends Model
{
    protected $table = 'larai_budgets';

    protected $fillable = [
        'amount',
        'currency_code',
        'alert_threshold',
        'recipient_email',
        'is_active',
        'last_alerted_at',
        'last_alert_period',
        'last_alert_threshold',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'alert_threshold' => 'integer',
        'is_active' => 'boolean',
        'last_alerted_at' => 'datetime',
        'last_alert_threshold' => 'integer',
    ];
}
