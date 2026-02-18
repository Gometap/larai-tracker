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
    }
}
