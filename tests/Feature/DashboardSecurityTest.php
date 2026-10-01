<?php

use Gometap\LaraiTracker\Models\LaraiLog;
use Gometap\LaraiTracker\Models\LaraiModelPrice;
use Gometap\LaraiTracker\Tests\TestCase;
use Illuminate\Support\Facades\Http;

uses(TestCase::class);

beforeEach(function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.password', 'secret');
    $this->withSession([
        'larai_authenticated' => true,
        'larai_auth_time' => time(),
    ]);
});

test('logs only accepts allowlisted sort fields and directions', function (array $query) {
    $this->get(route('larai.logs', $query))->assertSessionHasErrors(array_key_first($query));
})->with([
    'sort injection' => [['sort' => 'created_at; DROP TABLE larai_logs']],
    'direction injection' => [['direction' => 'sideways']],
]);

test('dashboard validates bounded date ranges', function () {
    $this->get(route('larai.dashboard', [
        'start_date' => 'not-a-date',
        'end_date' => '2099-01-01',
    ]))->assertSessionHasErrors(['start_date', 'end_date']);
});

test('settings rejects invalid numeric email currency and retention values', function () {
    $this->post(route('larai.settings.update'), [
        'budget' => [
            'amount' => -10,
            'threshold' => 101,
            'email' => 'not-an-email',
            'active' => '1',
        ],
        'currency' => ['code' => 'EUR', 'symbol' => '€'],
        'log_retention_days' => -1,
        'new_prices' => [[
            'provider' => str_repeat('a', 101),
            'model' => 'model',
            'input' => -1,
            'output' => INF,
        ]],
    ])->assertSessionHasErrors([
        'budget.amount',
        'budget.threshold',
        'budget.email',
        'currency.code',
        'log_retention_days',
        'new_prices.0.provider',
        'new_prices.0.input',
    ]);
});

test('csv export neutralizes spreadsheet formulas', function () {
    LaraiLog::create([
        'provider' => '=HYPERLINK("https://evil.test")',
        'model' => '@SUM(1+1)',
        'prompt_tokens' => 1,
        'completion_tokens' => 2,
        'total_tokens' => 3,
        'cost_usd' => null,
    ]);

    $content = $this->get(route('larai.export', ['format' => 'csv']))
        ->assertOk()
        ->streamedContent();

    expect($content)->toContain("'=HYPERLINK")
        ->toContain("'@SUM");
});

test('price sync validates catalog and preserves manual overrides', function () {
    LaraiModelPrice::create([
        'provider' => 'openai',
        'model' => 'gpt-4o',
        'input_price_per_1m' => 99,
        'output_price_per_1m' => 199,
        'is_custom' => true,
    ]);

    Http::fake([
        '*' => Http::response([
            'version' => 1,
            'source' => 'https://example.test/pricing',
            'effective_date' => '2026-09-01',
            'models' => [[
                'provider' => 'openai',
                'model' => 'gpt-4o',
                'input_price_per_1m' => 2.5,
                'output_price_per_1m' => 10,
            ]],
        ]),
    ]);

    $this->post(route('larai.sync-prices'))->assertSessionHas('success');

    $price = LaraiModelPrice::first();
    expect((float) $price->input_price_per_1m)->toBe(99.0)
        ->and($price->is_custom)->toBeTrue();
});

test('price sync rejects malformed remote catalogs', function () {
    Http::fake(['*' => Http::response([['provider' => 'openai']])]);

    $this->post(route('larai.sync-prices'))->assertSessionHas('error');

    expect(LaraiModelPrice::count())->toBe(0);
});
