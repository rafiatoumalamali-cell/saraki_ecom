<?php

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

beforeEach(function () {
    // Keep test artifacts out of the real backup directory.
    config(['backup.path' => storage_path('app/backups-test')]);
});

afterEach(function () {
    File::deleteDirectory(storage_path('app/backups-test'));
});

test('backup:run writes a readable database dump and an uploads archive', function () {
    $probe = new Process([config('backup.mysqldump'), '--version']);

    try {
        $probe->mustRun();
    } catch (Throwable) {
        $this->markTestSkipped('mysqldump is not available on this machine');
    }

    $this->artisan('backup:run')->assertSuccessful();

    $dumps = File::glob(storage_path('app/backups-test/db-*.sql.gz'));
    $uploads = File::glob(storage_path('app/backups-test/uploads-*.zip'));

    expect($dumps)->toHaveCount(1)
        ->and($uploads)->toHaveCount(1);

    // The gz must actually decompress into SQL, not just exist.
    $stream = gzopen($dumps[0], 'rb');
    $head = gzread($stream, 4096);
    gzclose($stream);

    expect($head)->toContain('MySQL dump');
});

test('backup:run prunes archives older than the retention window', function () {
    File::ensureDirectoryExists(storage_path('app/backups-test'));

    $old = storage_path('app/backups-test/db-20000101-000000.sql.gz');
    $fresh = storage_path('app/backups-test/db-20990101-000000.sql.gz');
    File::put($old, 'old');
    File::put($fresh, 'fresh');
    touch($old, now()->subDays(30)->getTimestamp());

    // The command needs mysqldump for the DB half; skip the whole run when absent.
    $probe = new Process([config('backup.mysqldump'), '--version']);

    try {
        $probe->mustRun();
    } catch (Throwable) {
        $this->markTestSkipped('mysqldump is not available on this machine');
    }

    $this->artisan('backup:run', ['--keep-days' => 14])->assertSuccessful();

    expect(File::exists($old))->toBeFalse()
        ->and(File::exists($fresh))->toBeTrue();
});
