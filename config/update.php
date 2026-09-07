<?php

return [

    'github' => [
        'owner' => env('GITHUB_REPO_OWNER', 'faveosuite'),
        'repo'  => env('GITHUB_REPO_NAME', 'faveo-helpdesk'),
        'token' => env('GITHUB_ACCESS_TOKEN'),
    ],

    'temp_directory' => 'UPDATES',

    'excluded_paths' => [
    ],

];
