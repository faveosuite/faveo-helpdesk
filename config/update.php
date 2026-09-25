<?php

return [

    'github' => [
        'owner' => env('GITHUB_REPO_OWNER', 'faveosuite'),
        'repo'  => env('GITHUB_REPO_NAME', 'faveo-helpdesk'),
        'token' => env('GITHUB_ACCESS_TOKEN'),
    ],

    'temp_directory' => 'UPDATES',

    // Never overwritten by an update. A trailing "/" excludes a whole directory;
    // a bare file name (no "/") matches that name in any directory.
    'excluded_paths' => [
        '.env',
        'storage/app/private.key',
        'storage/app/public.key',
        'storage/backups/',
        'storage/logs/',
        'storage/framework/',
        'UPDATES/',
    ],

];
