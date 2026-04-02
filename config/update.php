<?php

return [

    'github' => [
        'owner' => env('GITHUB_REPO_OWNER', 'faveosuite'),
        'repo'  => env('GITHUB_REPO_NAME', 'faveo-helpdesk'),
        'token' => env('GITHUB_ACCESS_TOKEN'),
    ],

    'temp_directory' => 'UPDATES',

    // Set to true to test auto-update without GitHub.
    // Place a test zip manually in UPDATES/latest-release.zip
    'test_mode' => env('UPDATE_TEST_MODE', false),

    'min_memory_mb' => 256,

    'excluded_paths' => [
        '.env',
        '.github/',
        '.idea/',
        'storage/',
        'config/database.php',
        'bootstrap/cache/',
    ],

];
