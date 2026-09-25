<?php

return  [
    'barryvdh/laravel-debugbar' => [
        'aliases' => [
            'Debugbar' => 'Barryvdh\\Debugbar\\Facades\\Debugbar',
        ],
        'providers' => [
            0 => 'Barryvdh\\Debugbar\\ServiceProvider',
        ],
    ],
    'beyondcode/laravel-query-detector' => [
        'providers' => [
            0 => 'BeyondCode\\QueryDetector\\QueryDetectorServiceProvider',
        ],
    ],
    'brozot/laravel-fcm' => [
        'providers' => [
            0 => 'LaravelFCM\\FCMServiceProvider',
        ],
        'aliases' => [
            'FCM'      => 'LaravelFCM\\Facades\\FCM',
            'FCMGroup' => 'LaravelFCM\\Facades\\FCMGroup',
        ],
    ],
    'chumper/zipper' => [
        'providers' => [
            0 => 'Chumper\\Zipper\\ZipperServiceProvider',
        ],
        'aliases' => [
            'Zipper' => 'Chumper\\Zipper\\Zipper',
        ],
    ],
    'diglactic/laravel-breadcrumbs' => [
        'aliases' => [
            'Breadcrumbs' => 'Diglactic\\Breadcrumbs\\Breadcrumbs',
        ],
        'providers' => [
            0 => 'Diglactic\\Breadcrumbs\\ServiceProvider',
        ],
    ],
    'itsgoingd/clockwork' => [
        'providers' => [
            0 => 'Clockwork\\Support\\Laravel\\ClockworkServiceProvider',
        ],
        'aliases' => [
            'Clockwork' => 'Clockwork\\Support\\Laravel\\Facade',
        ],
    ],
    'laravel/passport' => [
        'providers' => [
            0 => 'Laravel\\Passport\\PassportServiceProvider',
        ],
    ],
    'laravel/socialite' => [
        'aliases' => [
            'Socialite' => 'Laravel\\Socialite\\Facades\\Socialite',
        ],
        'providers' => [
            0 => 'Laravel\\Socialite\\SocialiteServiceProvider',
        ],
    ],
    'laravel/tinker' => [
        'providers' => [
            0 => 'Laravel\\Tinker\\TinkerServiceProvider',
        ],
    ],
    'laravel/ui' => [
        'providers' => [
            0 => 'Laravel\\Ui\\UiServiceProvider',
        ],
    ],
    'maatwebsite/excel' => [
        'aliases' => [
            'Excel' => 'Maatwebsite\\Excel\\Facades\\Excel',
        ],
        'providers' => [
            0 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
        ],
    ],
    'nesbot/carbon' => [
        'providers' => [
            0 => 'Carbon\\Laravel\\ServiceProvider',
        ],
    ],
    'nunomaduro/collision' => [
        'providers' => [
            0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
        ],
    ],
    'nunomaduro/termwind' => [
        'providers' => [
            0 => 'Termwind\\Laravel\\TermwindServiceProvider',
        ],
    ],
    'propaganistas/laravel-phone' => [
        'providers' => [
            0 => 'Propaganistas\\LaravelPhone\\PhoneServiceProvider',
        ],
    ],
    'sentry/sentry-laravel' => [
        'aliases' => [
            'Sentry' => 'Sentry\\Laravel\\Facade',
        ],
        'providers' => [
            0 => 'Sentry\\Laravel\\ServiceProvider',
            1 => 'Sentry\\Laravel\\Tracing\\ServiceProvider',
        ],
    ],
    'spatie/laravel-html' => [
        'aliases' => [
            'Html' => 'Spatie\\Html\\Facades\\Html',
        ],
        'providers' => [
            0 => 'Spatie\\Html\\HtmlServiceProvider',
        ],
    ],
    'spatie/laravel-ignition' => [
        'aliases' => [
            'Flare' => 'Spatie\\LaravelIgnition\\Facades\\Flare',
        ],
        'providers' => [
            0 => 'Spatie\\LaravelIgnition\\IgnitionServiceProvider',
        ],
    ],
    'thomaswelton/laravel-gravatar' => [
        'providers' => [
            0 => 'Thomaswelton\\LaravelGravatar\\LaravelGravatarServiceProvider',
        ],
        'aliases' => [
            'Gravatar' => 'Thomaswelton\\LaravelGravatar\\Facades\\Gravatar',
        ],
    ],
    'torann/geoip' => [
        'aliases' => [
            'GeoIP' => 'Torann\\GeoIP\\Facades\\GeoIP',
        ],
        'providers' => [
            0 => 'Torann\\GeoIP\\GeoIPServiceProvider',
        ],
    ],
    'unisharp/laravel-ckeditor' => [
        'providers' => [
            0 => 'Unisharp\\Ckeditor\\ServiceProvider',
        ],
    ],
    'unisharp/laravel-filemanager' => [
        'aliases' => [
        ],
        'providers' => [
            0 => 'UniSharp\\LaravelFilemanager\\LaravelFilemanagerServiceProvider',
        ],
    ],
    'yajra/laravel-datatables-oracle' => [
        'aliases' => [
            'DataTables' => 'Yajra\\DataTables\\Facades\\DataTables',
        ],
        'providers' => [
            0 => 'Yajra\\DataTables\\DataTablesServiceProvider',
        ],
    ],
];
