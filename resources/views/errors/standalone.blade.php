{{--
    Self-contained error page: no layout, no database, no helpers that read settings.

    Used whenever the branded error pages (errors.404 / errors.500 / errors.db, which
    extend the client layout and query settings_system, widgets, kb pages...) cannot
    be rendered: before the installer has written the database settings, or when the
    database is unreachable. Rendering a DB-backed page there only throws a second
    exception from inside the exception handler.

    Expects: $status (int)
--}}
@php
    $messages = [
        404 => Lang::get('lang.we_are_sorry_but_the_page_you_are_looking_for_can_not_be_found'),
        503 => Lang::get('lang.error_establishing_connection_to_database'),
    ];
    $message = $messages[$status] ?? Lang::get('lang.sorry_something_went_wrong');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ isRtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $status }} | {{ strip_tags($message) }}</title>
    <style>
        html, body { height: 100%; margin: 0; }
        body {
            display: flex; align-items: center; justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f4f6f9; color: #343a40; text-align: center; padding: 0 16px;
        }
        h1 { font-size: 96px; font-weight: 300; margin: 0; color: #17a2b8; }
        h2 { font-size: 20px; font-weight: 400; margin: 8px 0 24px; color: #6c757d; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main>
        <h1>{{ $status }}</h1>
        <h2>{!! $message !!}</h2>
        <p><a href="javascript:history.back()">{!! Lang::get('lang.go_back') !!}</a></p>
    </main>
</body>
</html>
