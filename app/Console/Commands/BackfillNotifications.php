<?php

namespace App\Console\Commands;

use App\Services\SystemNotificationService;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class BackfillNotifications extends Command
{
    protected $signature = 'notifications:backfill {--since=} {--limit=} {--dry-run}';

    protected $description = 'Backfill system notifications from activity_log history.';

    public function handle(SystemNotificationService $service): int
    {
        $since = $this->option('since');
        $limit = $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        if (! $since) {
            $since = now()->subDays(30)->toDateString();
        }

        $query = Activity::query()->orderBy('id');

        $query->whereDate('created_at', '>=', $since);

        if ($limit) {
            $query->limit((int) $limit);
        }

        $total = 0;
        $created = 0;

        $this->info('Backfilling notifications from activity_log...');

        $query->chunkById(200, function ($activities) use ($service, $dryRun, &$total, &$created) {
            foreach ($activities as $activity) {
                $total++;
                if ($dryRun) {
                    continue;
                }

                if ($service->notifyFromActivity($activity)) {
                    $created++;
                }
            }
        });

        $this->line('Activities processed: ' . $total);
        $this->line('Notifications created: ' . $created);

        return Command::SUCCESS;
    }
}
