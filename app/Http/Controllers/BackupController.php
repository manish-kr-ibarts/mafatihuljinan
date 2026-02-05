<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function backup($token)
    {
        // 1. Security check
        if ($token !== config('app.backup_token')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // 2. Get DB configuration
        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        if (!$dbConfig || $dbConfig['driver'] !== 'mysql') {
            return response()->json(['status' => 'error', 'message' => 'Only MySQL backups are supported'], 400);
        }

        $db   = $dbConfig['database'];
        $user = $dbConfig['username'];
        $pass = $dbConfig['password'];
        $host = $dbConfig['host'];

        // 3. Prepare paths
        $date = now()->format('Y-m-d_H-i-s');
        // Use DIRECTORY_SEPARATOR for true OS-agnostic paths
        $dir = storage_path('app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'dbbackup');

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $sqlFile = "db_backup_{$db}_{$date}.sql";
        $sqlPath = $dir . DIRECTORY_SEPARATOR . $sqlFile;

        // 4. Construct Shell-Safe Command
        $mysqldump = config('database.mysqldump_path', 'mysqldump');
        
        $userArg = escapeshellarg($user);
        $hostArg = escapeshellarg($host);
        $dbArg   = escapeshellarg($db);
        $pathArg = escapeshellarg($sqlPath);
        
        // Handle password (no space after -p)
        $passwordPart = ($pass !== '' && $pass !== null) ? "-p" . escapeshellarg($pass) : "";
        
        // Quote the binary if it has spaces and isn't already quoted
        $binaryLine = (strpos($mysqldump, ' ') !== false && strpos($mysqldump, '"') === false) 
                      ? "\"{$mysqldump}\"" 
                      : $mysqldump;

        $cmd = "{$binaryLine} --user={$userArg} {$passwordPart} --host={$hostArg} {$dbArg} > {$pathArg} 2>&1";

        // 5. Execute
        $output = [];
        $returnVar = null;
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Database backup failed',
                'debug'   => [
                    'command' => $cmd,
                    'return_code' => $returnVar,
                    'output' => $output,
                    'binary_path' => $mysqldump,
                    'binary_exists' => file_exists($mysqldump)
                ]
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Database backup created successfully',
            'file'    => $sqlFile,
            'url'     => route('admin.backup.download', ['token' => $token, 'file' => $sqlFile])
        ]);
    }

    public function download($token, $file)
    {
        // Security check
        if ($token !== config('app.backup_token')) {
            abort(403);
        }

        // Filename validation
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $file)) {
            abort(400, 'Invalid filename');
        }

        $path = storage_path('app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'dbbackup' . DIRECTORY_SEPARATOR . $file);

        if (File::exists($path)) {
            return response()->download($path);
        }

        abort(404, 'Backup file not found.');
    }
}
