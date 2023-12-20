<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Illuminate\Support\Facades\Route;

Breadcrumbs::for('logs', function (BreadcrumbTrail $trail) {
    $trail->parent('setting');
    $trail->push('System Logs', route('logs'));
});
Route::middleware('web', 'auth', 'roles')->group(function () {
    Route::get('logs', [\App\FaveoLog\controllers\LogViewerController::class, 'index'])->name('logs');
});
