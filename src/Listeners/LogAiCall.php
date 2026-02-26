<?php

namespace Gometap\LaraiTracker\Listeners;

use Gometap\LaraiTracker\Events\AiCallRecorded;
use Gometap\LaraiTracker\Models\LaraiLog;
use Gometap\LaraiTracker\Services\LaraiCostCalculator;

class LogAiCall
{
    public function __construct(
        protected LaraiCostCalculator $calculator
    ) {}

    /**
     * Handle the event.
     */
    public function handle(AiCallRecorded $event): void
    {
        $cost = $this->calculator->calculate(
            $event->provider,
            $event->model,
            $event->promptTokens,
            $event->completionTokens
        );

        LaraiLog::create([
            'user_id' => $event->userId,
            'provider' => $event->provider,
            'model' => $event->model,
            'prompt_tokens' => $event->promptTokens,
            'completion_tokens' => $event->completionTokens,
            'total_tokens' => $event->promptTokens + $event->completionTokens,
            'cost_usd' => $cost,
        ]);

        $this->checkBudget();
    }

    /**
     * Check monthly budget and send alert if needed.
     */
    protected function checkBudget(): void
    {
        try {
            $budget = \Gometap\LaraiTracker\Models\LaraiBudget::where('is_active', true)->first();
            
            if (!$budget || !$budget->recipient_email) {
                return;
            }

            // Don't alert more than once every 24 hours
            if ($budget->last_alerted_at && $budget->last_alerted_at->gt(now()->subDay())) {
                return;
            }

            $currentCost = LaraiLog::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('cost_usd');

            $thresholdAmount = ($budget->amount * $budget->alert_threshold) / 100;

            if ($currentCost >= $thresholdAmount) {
                $symbol = \Gometap\LaraiTracker\Models\LaraiSetting::get('currency_symbol', '$');
                \Illuminate\Support\Facades\Mail::to($budget->recipient_email)
                    ->send(new \Gometap\LaraiTracker\Mail\BudgetExceeded($budget, $currentCost, $symbol));

                $budget->update(['last_alerted_at' => now()]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Larai Tracker Budget Failure: ' . $e->getMessage());
        }
    }
}
