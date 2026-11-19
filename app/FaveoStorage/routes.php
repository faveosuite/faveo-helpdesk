<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

Event::listen('settings.system', function () {
    $controller = new \App\FaveoStorage\Controllers\SettingsController();
    echo $controller->settingsIcon();
});

Route::middleware('web')->group(function () {
    // Not "storage": `storage:link` creates public/storage, and Apache serves that
    // directory instead of routing to Laravel (public/.htaccess: !-d), so the page
    // answered "403 Forbidden". Same reason the paid edition's page lives elsewhere.
    // Route names are unchanged, so route('storage') / route('post.storage') still work.
    Route::get('storage-settings', [App\FaveoStorage\Controllers\SettingsController::class, 'settings'])->name('storage');
    Route::post('storage-settings', [App\FaveoStorage\Controllers\SettingsController::class, 'postSettings'])->name('post.storage');
    // Old bookmarks: reachable only where public/storage does not exist.
    Route::get('storage', fn () => redirect()->route('storage', [], 301));
    Route::get('attachment', [App\FaveoStorage\Controllers\SettingsController::class, 'attachment'])->name('attach');
});
