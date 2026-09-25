<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

Event::listen('settings.system', function () {
    $controller = new \App\FaveoStorage\Controllers\SettingsController();
    echo $controller->settingsIcon();
});

Route::middleware('web')->group(function () {
    Route::get('storage', [App\FaveoStorage\Controllers\SettingsController::class, 'settings'])->name('storage');
    Route::post('storage', [App\FaveoStorage\Controllers\SettingsController::class, 'postSettings'])->name('post.storage');
    Route::get('attachment', [App\FaveoStorage\Controllers\SettingsController::class, 'attachment'])->name('attach');
});
