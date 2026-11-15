<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Replays a backup:run archive. Destructive by nature — the database is
 * dropped and rebuilt from the dump, uploads are overwritten — so --force is
 * mandatory and there is no interactive shortcut.
 */
class BackupRestore extends Command
{
    protected $signature = 'backup:restore
        {target : db, uploads, or all}
        {--file= : Specific archive; defaults to the newest of its kind}
        {--force : Required; confirms the overwrite}';

    protected $description = 'Restore the database and/or uploads from a backup archive';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->error('Refusing to restore without --force: this overwrites the database and uploads.');

            return self::FAILURE;
        }

        $target = strtolower((string) $this->argument('target'));

        if (! in_array($target, ['db', 'uploads', 'all'], true)) {
            $this->error('Target must be db, uploads, or all.');

            return self::FAILURE;
        }

        $ok = true;

        if (in_array($target, ['db', 'all'], true)) {
            $ok = $this->restoreDatabase() && $ok;
        }

        if (in_array($target, ['uploads', 'all'], true)) {
            $ok = $this->restoreUploads() && $ok;
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function restoreDatabase(): bool
    {
        $file = $this->resolve('db-*.sql.gz');

        if ($file === null) {
            $this->error('No database backup found.');

            return false;
        }

        $this->info("Restoring database from {$file}");

        $connection = config('database.connections.'.config('database.default'));

        // Laravel splits mysql/mariadb drivers; the mysql client serves both.
        if (! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            $this->error('backup:restore only supports mysql/mariadb connections.');

            return false;
        }

        $command = [
            config('backup.mysql'),
            '--host='.($connection['host'] ?? '127.0.0.1'),
            '--port='.($connection['port'] ?? 3306),
            '--user='.($connection['username'] ?? 'root'),
            $connection['database'],
        ];

        $env = isset($connection['password']) && $connection['password'] !== ''
            ? ['MYSQL_PWD' => $connection['password']]
            : [];

        $stream = gzopen($file, 'rb');

        if ($stream === false) {
            $this->error("Cannot read {$file}");

            return false;
        }

        $process = new Process($command, null, $env);
        $process->setInput($stream);
        $process->run();
        gzclose($stream);

        if (! $process->isSuccessful()) {
            $this->error('mysql restore failed: '.trim($process->getErrorOutput()));

            return false;
        }

        return true;
    }

    private function restoreUploads(): bool
    {
        $file = $this->resolve('uploads-*.zip');

        if ($file === null) {
            $this->error('No uploads backup found.');

            return false;
        }

        $this->info("Restoring uploads from {$file}");

        $zip = new ZipArchive;

        if ($zip->open($file) !== true) {
            $this->error("Cannot read {$file}");

            return false;
        }

        // Entries are rooted at public/, so extracting into storage/app lands
        // them back at storage/app/public/.
        $zip->extractTo(storage_path('app'));
        $zip->close();

        return true;
    }

    private function resolve(string $pattern): ?string
    {
        if ($explicit = $this->option('file')) {
            $path = str_starts_with($explicit, '/') || preg_match('/^[A-Za-z]:[\\\\\\/]/', $explicit)
                ? $explicit
                : config('backup.path').'/'.$explicit;

            return is_file($path) ? $path : null;
        }

        $files = File::glob(config('backup.path').'/'.$pattern) ?: [];

        if ($files === []) {
            return null;
        }

        usort($files, fn (string $a, string $b) => filemtime($b) <=> filemtime($a));

        return $files[0];
    }
}
