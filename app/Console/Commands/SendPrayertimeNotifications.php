<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\Common\PrayertimeNotificationController;

class SendPrayertimeNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prayertime-notifications:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled prayer time notifications based on user timezone and location';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting prayer time notification service...');

        try {
            app(PrayertimeNotificationController::class)->sendScheduledNotification();
            $this->info('Prayer time notifications processed successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to process prayer time notifications: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
