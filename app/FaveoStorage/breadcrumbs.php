<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('storage', function (BreadcrumbTrail $trail) {
    $trail->parent('setting');
    $trail->push('Storage', route('storage'));
});
