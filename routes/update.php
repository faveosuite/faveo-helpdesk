<?php

/**
 * upgrade from 1.9.6 to 1.9.7.
 */
Route::get('1-9-7', function () {
    if (\Schema::hasTable('ticket_type')) {
        return redirect('/')->with(['success' => 'You are application is up to date']);
    }
    \Artisan::call('migrate', ['--force' => true]);
    \Artisan::call('db:seed', ['--class' => 'TickettypeSeeder', '--force' => true]);
    \Artisan::call('db:seed', ['--class' => 'CustomFormSeeder', '--force' => true]);
    \Artisan::call('view:clear');

    return redirect('/')->with(['success' => 'Application has upgraded to 1.9.7 from 1.9.6']);
});
