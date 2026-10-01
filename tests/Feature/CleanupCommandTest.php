<?php

use Gometap\LaraiTracker\Models\LaraiLog;
use Gometap\LaraiTracker\Models\LaraiSetting;
use Gometap\LaraiTracker\Tests\TestCase;

uses(TestCase::class);

test('scheduled cleanup removes only logs older than retention', function () {
    LaraiSetting::set('log_retention_days', 30);

    $old = LaraiLog::create([
        'provider' => 'openai', 'model' => 'gpt-4o',
        'prompt_tokens' => 1, 'completion_tokens' => 1, 'total_tokens' => 2,
        'cost_usd' => 0.1,
    ]);
    $old->forceFill(['created_at' => now()->subDays(31), 'updated_at' => now()->subDays(31)])->save();

    LaraiLog::create([
        'provider' => 'openai', 'model' => 'gpt-4o',
        'prompt_tokens' => 1, 'completion_tokens' => 1, 'total_tokens' => 2,
        'cost_usd' => 0.1,
    ]);

    $this->artisan('larai:cleanup')->assertSuccessful();

    expect(LaraiLog::count())->toBe(1);
});
