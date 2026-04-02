<?php

return [

    'github' => [
        'owner' => env('GITHUB_REPO_OWNER', 'faveosuite'),
        'repo'  => env('GITHUB_REPO_NAME', 'faveo-helpdesk'),
        'token' => env('GITHUB_ACCESS_TOKEN'),
    ],

    'temp_directory' => 'UPDATES',

    'min_memory_mb' => 256,

    'excluded_paths' => [
        '.env',
        'storage/',
        'config/database.php',
        'bootstrap/cache/',
    ],

];
