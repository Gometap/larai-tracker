<?php

use Gometap\LaraiTracker\Events\AiCallRecorded;
use Gometap\LaraiTracker\Mail\BudgetExceeded;
use Gometap\LaraiTracker\Models\LaraiBudget;
use Gometap\LaraiTracker\Models\LaraiLog;
use Gometap\LaraiTracker\Tests\TestCase;
use Illuminate\Support\Facades\Mail;

uses(TestCase::class);

test('it stores a null cost when the model price is unavailable', function () {
    AiCallRecorded::dispatch(null, 'openai', 'future-unknown-model', 10, 20);

    $log = LaraiLog::firstOrFail();
    expect($log->total_tokens)->toBe(30)
        ->and($log->cost_usd)->toBeNull();
});

test('it sends only one budget alert for a threshold in a billing period', function () {
    Mail::fake();
    LaraiBudget::create([
        'amount' => 1,
        'currency_code' => 'USD',
        'alert_threshold' => 50,
        'recipient_email' => 'owner@example.test',
        'is_active' => true,
    ]);

    AiCallRecorded::dispatch(null, 'openai', 'gpt-4o', 100000, 0);
    AiCallRecorded::dispatch(null, 'openai', 'gpt-4o', 100000, 0);
    AiCallRecorded::dispatch(null, 'openai', 'gpt-4o', 100000, 0);

    Mail::assertSent(BudgetExceeded::class, 1);
    expect(LaraiBudget::first()->last_alert_period)->toBe(now()->format('Y-m'));
});
