<?php

namespace App\Console\Commands\HijriNotification;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\Common\PrayertimeNotificationController;

class syncPrayerTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hijri-notification:sync-prayer-times';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync prayer times for hijri notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing prayer times for hijri notification...');

        try {
            app(PrayertimeNotificationController::class)->syncPrayerTimes();
            $this->info('Prayer times synced successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to sync prayer times: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
