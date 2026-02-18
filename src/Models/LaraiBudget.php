<?php

namespace Gometap\LaraiTracker\Models;

use Illuminate\Database\Eloquent\Model;

class LaraiBudget extends Model
{
    protected $table = 'larai_budgets';

    protected $fillable = [
        'amount',
        'alert_threshold',
        'recipient_email',
        'is_active',
        'last_alerted_at',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'alert_threshold' => 'integer',
        'is_active' => 'boolean',
        'last_alerted_at' => 'datetime',
    ];
}
