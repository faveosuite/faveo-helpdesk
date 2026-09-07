<?php

/**
 * upgrade from 1.9.6 to 1.9.7.
 */
Route::middleware(['auth', 'roles'])->get('1-9-7', function () {
    if (!\Auth::check() || \Auth::user()->role !== 'admin') {
        abort(403, 'Forbidden');
    }
    if (\Schema::hasTable('ticket_type')) {
        return redirect('/')->with(['success' => 'You are application is up to date']);
    }
    \Artisan::call('migrate', ['--force' => true]);
    \Artisan::call('db:seed', ['--class' => 'TickettypeSeeder', '--force' => true]);
    \Artisan::call('db:seed', ['--class' => 'CustomFormSeeder', '--force' => true]);
    \Artisan::call('view:clear');

    return redirect('/')->with(['success' => 'Application has upgraded to 1.9.7 from 1.9.6']);
});
