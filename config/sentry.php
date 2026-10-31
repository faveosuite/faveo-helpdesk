<?php

return [

    // Production only. Whether crash reports are actually sent is the admin's choice (Admin > Error and
    // debugging), applied per event by `before_send` below so it takes effect without a restart.
    'dsn' => env('APP_ENV') === 'production'
        ? env('SENTRY_LARAVEL_DSN', 'https://acfddf6f39ab992a80912cd4ae30c2ae@o4510996661338112.ingest.de.sentry.io/4511115707744336')
        : null,

    'before_send' => [\App\Support\SentryGate::class, 'beforeSend'],

    // Tag every event with the product version (e.g. v9.4.3.6.RC.3) so issues can be
    // filtered by release in Sentry
    'release' => (parse_ini_file(dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'faveoconfig.ini', false, INI_SCANNER_RAW) ?: [])['APP_VERSION'] ?? null,

    // Off by default; the admin "Send performance reports" setting (APP_SENTRY_PERFORMANCE) turns on
    // 10% sampling. SENTRY_TRACES_SAMPLE_RATE, when set, takes precedence.
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', env('APP_SENTRY_PERFORMANCE', false) ? 0.1 : 0),

    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0),

    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    'environment' => env('APP_ENV', 'production'),

    // Performance monitoring tracing configuration
    'tracing' => [
        // Capture SQL queries as spans (required for "Queries by Time Spent" in Sentry Insights)
        'sql_queries' => env('SENTRY_TRACE_SQL_QUERIES_ENABLED', true),

        // Capture SQL query bindings (parameters) in SQL query spans
        'sql_bindings' => env('SENTRY_TRACE_SQL_BINDINGS_ENABLED', false),

        // Capture where the SQL query originated from on the SQL query spans
        'sql_origin' => env('SENTRY_TRACE_SQL_ORIGIN_ENABLED', true),

        // Define a threshold in milliseconds for SQL queries to resolve their origin
        'sql_origin_threshold_ms' => env('SENTRY_TRACE_SQL_ORIGIN_THRESHOLD_MS', 100),

        // Capture Laravel cache events as spans (required for "Cache Miss Rates" in Sentry Insights)
        'cache' => env('SENTRY_TRACE_CACHE_ENABLED', true),

        // Capture Redis operations as spans
        'redis_commands' => env('SENTRY_TRACE_REDIS_COMMANDS', false),

        // Capture HTTP client requests as spans
        'http_client_requests' => env('SENTRY_TRACE_HTTP_CLIENT_REQUESTS_ENABLED', true),

        // Capture views rendered as spans
        'views' => env('SENTRY_TRACE_VIEWS_ENABLED', true),

        // Trace queue jobs as their own transactions
        'queue_job_transactions' => env('SENTRY_TRACE_QUEUE_ENABLED', true),

        // Capture queue jobs as spans when executed on the sync driver
        'queue_jobs' => env('SENTRY_TRACE_QUEUE_JOBS_ENABLED', true),

        // Capture send notifications as spans
        'notifications' => env('SENTRY_TRACE_NOTIFICATIONS_ENABLED', true),

        // Continue trace after response has been sent (needed for afterResponse queue jobs)
        'continue_after_response' => env('SENTRY_TRACE_CONTINUE_AFTER_RESPONSE', true),

        // Enable the tracing integrations supplied by Sentry (recommended)
        'default_integrations' => env('SENTRY_TRACE_DEFAULT_INTEGRATIONS_ENABLED', true),
    ],

    // Breadcrumb configuration
    'breadcrumbs' => [
        // Capture Laravel logs as breadcrumbs
        'logs' => env('SENTRY_BREADCRUMBS_LOGS_ENABLED', true),

        // Capture Laravel cache events as breadcrumbs
        'cache' => env('SENTRY_BREADCRUMBS_CACHE_ENABLED', true),

        // Capture SQL queries as breadcrumbs
        'sql_queries' => env('SENTRY_BREADCRUMBS_SQL_QUERIES_ENABLED', true),

        // Capture SQL query bindings in breadcrumbs
        'sql_bindings' => env('SENTRY_BREADCRUMBS_SQL_BINDINGS_ENABLED', false),

        // Capture queue job information as breadcrumbs
        'queue_info' => env('SENTRY_BREADCRUMBS_QUEUE_INFO_ENABLED', true),

        // Capture HTTP client request information as breadcrumbs
        'http_client_requests' => env('SENTRY_BREADCRUMBS_HTTP_CLIENT_REQUESTS_ENABLED', true),

        // Capture send notifications as breadcrumbs
        'notifications' => env('SENTRY_BREADCRUMBS_NOTIFICATIONS_ENABLED', true),
    ],

];
