<?php

namespace Gometap\LaraiTracker\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiCallRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ?int $userId,
        public string $provider,
        public string $model,
        public int $promptTokens,
        public int $completionTokens
    ) {}
}
