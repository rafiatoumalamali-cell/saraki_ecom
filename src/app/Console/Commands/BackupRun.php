<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Nightly safety net: a gzipped mysqldump of the database plus a zip of the
 * public uploads disk, both under storage/app/backups (never web-reachable).
 * Files older than backup.keep_days are pruned on every run.
 */
class BackupRun extends Command
{
    protected $signature = 'backup:run {--keep-days= : Override the retention window}';

    protected $description = 'Write a timestamped database dump and uploads archive, then prune old backups';

    public function handle(): int
    {
        $path = config('backup.path');
        File::ensureDirectoryExists($path);

        $stamp = now()->format('Ymd-His');
        $results = [];

        $results[] = $this->dumpDatabase($path, $stamp);
        $results[] = $this->archiveUploads($path, $stamp);
        $pruned = $this->prune($path);

        foreach ($results as $file) {
            $this->info("Wrote {$file} (".number_format(filesize($file) / 1024, 1)." KB)");
        }

        $this->info("Pruned {$pruned} backup(s) older than ".($this->option('keep-days') ?? config('backup.keep_days')).' days.');

        return self::SUCCESS;
    }

    private function dumpDatabase(string $path, string $stamp): string
    {
        $connection = config('database.connections.'.config('database.default'));

        // Laravel splits mysql/mariadb drivers; mysqldump serves both.
        if (! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException('backup:run only supports mysql/mariadb connections; got '.($connection['driver'] ?? 'none'));
        }

        $target = $path.'/db-'.$stamp.'.sql.gz';
        $gz = gzopen($target, 'wb9');

        if ($gz === false) {
            throw new \RuntimeException("Cannot write {$target}");
        }

        $command = [
            config('backup.mysqldump'),
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--default-character-set=utf8mb4',
            '--host='.($connection['host'] ?? '127.0.0.1'),
            '--port='.($connection['port'] ?? 3306),
            '--user='.($connection['username'] ?? 'root'),
            '--databases',
            $connection['database'],
        ];

        $env = isset($connection['password']) && $connection['password'] !== ''
            ? ['MYSQL_PWD' => $connection['password']]
            : [];

        $process = new Process($command, null, $env);
        $process->run(function (string $type, string $buffer) use ($gz): void {
            // stderr must never land inside the dump stream.
            if ($type === Process::OUT) {
                gzwrite($gz, $buffer);
            }
        });
        gzclose($gz);

        if (! $process->isSuccessful()) {
            @unlink($target);

            throw new \RuntimeException('mysqldump failed: '.trim($process->getErrorOutput()));
        }

        return $target;
    }

    private function archiveUploads(string $path, string $stamp): string
    {
        $target = $path.'/uploads-'.$stamp.'.zip';
        $zip = new ZipArchive;

        if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Cannot write {$target}");
        }

        // The 'public' disk root is storage/app/public.
        $root = storage_path('app/public');

        foreach (File::allFiles($root) as $file) {
            $local = $file->getPathname();
            $name = 'public/'.str_replace('\\', '/', substr($local, strlen($root) + 1));

            $zip->addFile($local, $name);
        }

        $zip->close();

        return $target;
    }

    private function prune(string $path): int
    {
        // 0 would prune the archives written moments ago, so the floor is 1.
        $keepDays = max(1, (int) ($this->option('keep-days') ?? config('backup.keep_days')));
        $cutoff = now()->subDays($keepDays)->getTimestamp();
        $pruned = 0;

        // GLOB_BRACE is not portable to Windows, so the two patterns run separately.
        $files = array_merge(
            File::glob($path.'/db-*.sql.gz') ?: [],
            File::glob($path.'/uploads-*.zip') ?: [],
        );

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                File::delete($file);
                $pruned++;
            }
        }

        return $pruned;
    }
}
