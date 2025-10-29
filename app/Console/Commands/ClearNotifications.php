<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class ClearNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clear {--force : Force clear without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all notifications from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Notification::count();

        if ($count === 0) {
            $this->info('No notifications to delete.');
            return 0;
        }

        $this->info("Found {$count} notifications.");

        if (!$this->option('force')) {
            if (!$this->confirm("⚠️  This will DELETE ALL {$count} notifications. Are you sure?")) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        Notification::query()->delete();

        $this->info("✅ Successfully deleted {$count} notifications!");

        return 0;
    }
}

