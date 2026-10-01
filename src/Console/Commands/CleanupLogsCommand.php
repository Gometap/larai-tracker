<?php

namespace Gometap\LaraiTracker\Console\Commands;

use Gometap\LaraiTracker\Models\LaraiLog;
use Gometap\LaraiTracker\Models\LaraiSetting;
use Illuminate\Console\Command;

class CleanupLogsCommand extends Command
{
    protected $signature = 'larai:cleanup {--days= : Override the configured retention period} {--batch=1000 : Rows deleted per batch}';

    protected $description = 'Delete Larai Tracker logs older than the configured retention period';

    public function handle(): int
    {
        $days = $this->option('days') !== null
            ? filter_var($this->option('days'), FILTER_VALIDATE_INT)
            : (int) LaraiSetting::get('log_retention_days', 0);
        $batchSize = filter_var($this->option('batch'), FILTER_VALIDATE_INT);

        if ($days === false || $days < 1 || $days > 3650) {
            $this->components->error('Retention days must be between 1 and 3650.');

            return self::INVALID;
        }

        if ($batchSize === false || $batchSize < 1 || $batchSize > 10000) {
            $this->components->error('Batch size must be between 1 and 10000.');

            return self::INVALID;
        }

        $cutoff = now()->subDays($days);
        $deleted = 0;

        do {
            $ids = LaraiLog::query()
                ->where('created_at', '<', $cutoff)
                ->orderBy('id')
                ->limit($batchSize)
                ->pluck('id');

            $batchDeleted = $ids->isEmpty()
                ? 0
                : LaraiLog::query()->whereIn('id', $ids)->delete();
            $deleted += $batchDeleted;
        } while ($batchDeleted === $batchSize);

        LaraiSetting::set('last_cleanup_at', now()->toDateTimeString());
        $this->components->info("Deleted {$deleted} expired Larai Tracker log(s).");

        return self::SUCCESS;
    }
}
