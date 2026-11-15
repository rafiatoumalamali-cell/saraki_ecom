<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup destination
    |--------------------------------------------------------------------------
    |
    | Backups live outside public/ so they are never web-reachable. The disk
    | is local files; point BACKUP_PATH elsewhere (e.g. a mounted volume on
    | the host) if the project directory itself is not retained.
    |
    */

    'path' => env('BACKUP_PATH', storage_path('app/backups')),

    // Daily database + uploads archives are kept for this many days.
    'keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Database dump binaries
    |--------------------------------------------------------------------------
    |
    | mysqldump/mysql ship with the database server, not with PHP. On shared
    | hosting they live in the MySQL bin directory — set the full path in
    | .env when they are not on PATH.
    |
    */

    'mysqldump' => env('BACKUP_MYSQLDUMP', 'mysqldump'),

    'mysql' => env('BACKUP_MYSQL', 'mysql'),

];
