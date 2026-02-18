<?php

namespace Gometap\LaraiTracker\Models;

use Illuminate\Database\Eloquent\Model;

class LaraiLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'larai_logs';
}
