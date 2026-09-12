<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseBackup extends Command
{
    protected $signature = 'backup:full';

    protected $description = 'Backup the database and project files, excluding vendor, node_modules, and the backups directory, and store it in the backup directory. Keep only the last 7 backups.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Artisan::call('optimize:clear');
        $backupDir = storage_path('app/backups');
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        $dbBackupFile = "{$backupDir}/db_backup_{$date}.sql";

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $excludeTables = [
            'telescope_entries',
            'telescope_entries_tags',
            'telescope_monitoring',
        ];

        $excludeTables = [];

        if ($this->backupDatabase($dbBackupFile, $excludeTables)) {
            $this->info("Database backup created successfully: {$dbBackupFile}");
        }

        $this->deleteOldBackups($backupDir);
    }

    private function backupDatabase($backupFile, array $excludeTables)
    {
        $dbHost = env('DB_HOST', 'localhost');
        $dbName = env('DB_DATABASE', 'forge');
        $dbUser = env('DB_USERNAME', 'forge');
        $dbPass = env('DB_PASSWORD', '');

        $ignoreTablesCommand = [];
        foreach ($excludeTables as $table) {
            $ignoreTablesCommand[] = "--ignore-table={$dbName}.{$table}";
        }

        $command = array_merge(
            [
                'mysqldump',
                '--user=' . $dbUser,
                '--password=' . $dbPass,
                '--host=' . $dbHost,
                '--single-transaction',   // consistent snapshot, doesn't lock InnoDB tables
                '--quick',                // stream rows instead of buffering, avoids OOM on big tables
                '--no-tablespaces',
                '--default-character-set=utf8mb4',
                '--result-file=' . $backupFile,
                $dbName,
            ],
            $ignoreTablesCommand
        );

        $process = new Process($command);

        try {
            $process->mustRun();

            // verify the dump actually finished cleanly
            $tail = shell_exec('tail -c 200 ' . escapeshellarg($backupFile));
            if (strpos($tail, '-- Dump completed') === false) {
                $this->error("Backup appears truncated: {$backupFile}");
                Storage::delete(str_replace(storage_path('app/'), '', $backupFile));
                return false;
            }

            return true;
        } catch (ProcessFailedException $exception) {
            $this->error('Error creating database backup: ' . $exception->getMessage());
            return false;
        }
    }

    private function backupProject($backupFile)
    {
        $projectRoot = base_path();

        $command = [
            'tar',
            '--exclude=vendor',
            '--exclude=node_modules',
            '--exclude=storage/app/backups',
            '-czf',
            $backupFile,
            '-C',
            $projectRoot,
            '.'
        ];

        $process = new Process($command);

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $exception) {
            $this->error('Error creating project backup: ' . $exception->getMessage());
            return false;
        }
    }

    private function zipBackups($zipFile, $dbBackupFile, $projectBackupFile)
    {
        $command = [
            'zip',
            '-j',
            $zipFile,
            $dbBackupFile,
            $projectBackupFile
        ];

        $process = new Process($command);

        try {
            $process->mustRun();
            unlink($dbBackupFile);
            unlink($projectBackupFile);
            return true;
        } catch (ProcessFailedException $exception) {
            $this->error('Error creating ZIP backup: ' . $exception->getMessage());
            return false;
        }
    }

    private function deleteOldBackups($backupDir)
    {
        $files = collect(Storage::files('backups'))
            ->sortByDesc(function ($file) {
                return filemtime(storage_path('app/' . $file));
            })
            ->values();

        if ($files->count() > 7) {
            $oldBackups = $files->slice(7);
            foreach ($oldBackups as $oldBackup) {
                Storage::delete($oldBackup);
                $this->info("Deleted old backup: " . storage_path('app/' . $oldBackup));
            }
        }
    }
}
