<?php

namespace App\Console\Commands\ActivityLog;

use Illuminate\Console\Command;
use App\Models\ActivityLog;
use Carbon\Carbon;

class CleanupActivityLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activitylog:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete activity logs older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = Carbon::now()->subDays(30);

        $count = ActivityLog::where('created_at', '<', $date)->delete();

        $this->info("Successfully deleted $count activity log(s) older than 30 days.");
    }
}
