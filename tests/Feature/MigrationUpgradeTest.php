<?php

use Gometap\LaraiTracker\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(TestCase::class);

test('v1.2 migration preserves legacy data and enables unknown costs', function () {
    Schema::drop('larai_logs');
    Schema::drop('larai_budgets');
    Schema::drop('larai_model_prices');

    Schema::create('larai_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable()->index();
        $table->string('provider')->index();
        $table->string('model')->index();
        $table->integer('prompt_tokens')->default(0);
        $table->integer('completion_tokens')->default(0);
        $table->integer('total_tokens')->default(0);
        $table->decimal('cost_usd', 15, 8)->default(0);
        $table->timestamps();
    });
    Schema::create('larai_budgets', function (Blueprint $table) {
        $table->id();
        $table->decimal('amount', 15, 4);
        $table->integer('alert_threshold')->default(80);
        $table->string('recipient_email')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamp('last_alerted_at')->nullable();
        $table->timestamps();
    });
    Schema::create('larai_model_prices', function (Blueprint $table) {
        $table->id();
        $table->string('provider');
        $table->string('model');
        $table->decimal('input_price_per_1m', 15, 4);
        $table->decimal('output_price_per_1m', 15, 4);
        $table->boolean('is_custom')->default(false);
        $table->timestamps();
        $table->unique(['provider', 'model']);
    });

    DB::table('larai_logs')->insert([
        'provider' => 'openai', 'model' => 'gpt-4o', 'cost_usd' => 1.25,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('larai_model_prices')->insert([
        'provider' => 'openai', 'model' => 'gpt-4o',
        'input_price_per_1m' => 99, 'output_price_per_1m' => 199,
        'is_custom' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $migration = include __DIR__.'/../../database/migrations/upgrade_larai_tracker_to_v1_2_0.php.stub';
    $migration->up();

    DB::table('larai_logs')->insert([
        'provider' => 'openai', 'model' => 'unknown', 'cost_usd' => null,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    expect(DB::table('larai_logs')->count())->toBe(2)
        ->and((float) DB::table('larai_logs')->first()->cost_usd)->toBe(1.25)
        ->and(DB::table('larai_model_prices')->first()->is_custom)->toBe(1)
        ->and(Schema::hasColumn('larai_budgets', 'currency_code'))->toBeTrue()
        ->and(Schema::hasColumn('larai_model_prices', 'source_url'))->toBeTrue()
        ->and(Schema::hasIndex('larai_logs', 'larai_logs_created_at_index'))->toBeTrue()
        ->and(Schema::hasIndex('larai_logs', 'larai_logs_provider_created_at_index'))->toBeTrue();
});
