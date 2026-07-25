<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class PruneNotifications extends Command
{
    protected $signature = 'notifications:prune {--days=30 : Number of days to keep}';
    protected $description = 'Prune old notifications to prevent database bloat';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $readCount = Notification::where('is_read', true)
            ->where('created_at', '<', $cutoffDate)
            ->count();

        $unreadCount = Notification::where('is_read', false)
            ->where('created_at', '<', $cutoffDate)
            ->count();

        if ($readCount === 0 && $unreadCount === 0) {
            $this->info('No notifications to prune.');
            return Command::SUCCESS;
        }

        if ($this->confirm("Delete {$readCount} read and {$unreadCount} unread notifications older than {$days} days?")) {
            $deleted = Notification::where('created_at', '<', $cutoffDate)->delete();
            $this->info("Successfully deleted {$deleted} notifications.");
        } else {
            $this->info('Pruning cancelled.');
        }

        return Command::SUCCESS;
    }
}
