<?php

namespace App\Console\Commands\Backup;

use Illuminate\Console\Command;

class CreateDBBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database backup and send it via email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = config('app.backup_token');

        if (!$token) {
            $this->error('Backup token not found in config. Please set BACKUP_TOKEN in your .env file.');
            return 1;
        }

        $this->info('Starting database backup...');

        $controller = new \App\Http\Controllers\BackupController();
        $response = $controller->backup($token);

        $data = json_decode($response->getContent(), true);

        if (isset($data['status']) && $data['status'] === 'success') {
            $this->info($data['message']);
            if (isset($data['file'])) {
                $this->line('File: ' . $data['file']);
            }
            return 0;
        } else {
            $this->error($data['message'] ?? 'Database backup failed.');
            if (isset($data['debug'])) {
                $this->line(json_encode($data['debug'], JSON_PRETTY_PRINT));
            }
            return 1;
        }
    }
}
