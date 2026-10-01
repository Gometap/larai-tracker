<?php

namespace Gometap\LaraiTracker\Listeners;

use Gometap\LaraiTracker\Events\AiCallRecorded;
use Gometap\LaraiTracker\Mail\BudgetExceeded;
use Gometap\LaraiTracker\Models\LaraiBudget;
use Gometap\LaraiTracker\Models\LaraiLog;
use Gometap\LaraiTracker\Services\LaraiCostCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

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
        try {
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
        } catch (Throwable $exception) {
            Log::warning('Larai Tracker could not record AI usage.', [
                'exception' => $exception::class,
            ]);
        }
    }

    /**
     * Check monthly budget and send alert if needed.
     */
    protected function checkBudget(): void
    {
        try {
            $alert = DB::transaction(function () {
                $budget = LaraiBudget::where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (! $budget || ! $budget->recipient_email || $budget->currency_code !== 'USD' || (float) $budget->amount <= 0) {
                    return null;
                }

                $period = now()->format('Y-m');
                if ($budget->last_alert_period === $period
                    && $budget->last_alert_threshold === $budget->alert_threshold) {
                    return null;
                }

                $currentCost = LaraiLog::whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ])->sum('cost_usd');
                $thresholdAmount = ((float) $budget->amount * $budget->alert_threshold) / 100;

                if ($currentCost < $thresholdAmount) {
                    return null;
                }

                $budget->update([
                    'last_alerted_at' => now(),
                    'last_alert_period' => $period,
                    'last_alert_threshold' => $budget->alert_threshold,
                ]);

                return [$budget, $currentCost];
            });

            if ($alert === null) {
                return;
            }

            [$budget, $currentCost] = $alert;
            $mailable = new BudgetExceeded($budget, $currentCost, '$');

            if (config('queue.default', 'sync') === 'sync') {
                Mail::to($budget->recipient_email)->send($mailable);
            } else {
                Mail::to($budget->recipient_email)->queue($mailable);
            }
        } catch (Throwable $exception) {
            Log::warning('Larai Tracker budget alert failed.', [
                'exception' => $exception::class,
            ]);
        }
    }
}
