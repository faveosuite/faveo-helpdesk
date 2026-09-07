<p align="center"><img src="https://raw.githubusercontent.com/diglactic/laravel-breadcrumbs/main/logo.svg" alt="Laravel Breadcrumbs"></p>

<p align="center">
    <a href="https://github.com/diglactic/laravel-breadcrumbs/actions?query=workflow%3Atests"><img alt="Build Status" src="https://img.shields.io/github/actions/workflow/status/diglactic/laravel-breadcrumbs/tests.yml?branch=main"></a>
    <a href="https://packagist.org/packages/diglactic/laravel-breadcrumbs"><img src="https://img.shields.io/packagist/dt/diglactic/laravel-breadcrumbs" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/diglactic/laravel-breadcrumbs"><img src="https://img.shields.io/packagist/v/diglactic/laravel-breadcrumbs" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/diglactic/laravel-breadcrumbs"><img src="https://img.shields.io/packagist/l/diglactic/laravel-breadcrumbs" alt="License"></a>
</p>

## Introduction

A simple Laravel-style way to create breadcrumbs.

This project is the official fork of the fantastically
original [Laravel Breadcrumbs by Dave James Miller](https://github.com/davejamesmiller/laravel-breadcrumbs) and wouldn't
have been possible
without [a bunch of awesome day-one contributors](https://github.com/davejamesmiller/laravel-breadcrumbs/graphs/contributors).
Thanks, all!


Table of Contents
-----------------

- [Compatibility Chart](#compatibility-chart)
- [Getting Started](#getting-started)
- [Defining Breadcrumbs](#defining-breadcrumbs)
- [Custom Templates](#custom-templates)
- [Outputting Breadcrumbs](#outputting-breadcrumbs)
- [Structured Data](#structured-data)
- [Route-Bound Breadcrumbs](#route-bound-breadcrumbs)
- [Advanced Usage](#advanced-usage)
- [FAQ](#faq)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

Compatibility Chart
-------------------

| Laravel | Laravel Breadcrumbs |
|---------|---------------------|
| 12.x    | 10.x                |
| 11.x    | 10.x                |
| 10.x    | 10.x                |
| 9.x     | 9.x                 |
| 8.x     | 9.x                 |
| 7.x     | 8.x                 |
| 6.x     | 8.x                 |

For older Laravel versions, reference
the [original GitHub project](https://github.com/davejamesmiller/laravel-breadcrumbs). All tags have been mirrored if
you prefer referencing this package, but will provide no functional difference.


Getting Started
---------------

### 1. Install

```bash
composer require diglactic/laravel-breadcrumbs
```

### 2. Define

Create a file called `routes/breadcrumbs.php` that looks like this:

```php
<?php // routes/breadcrumbs.php

// Note: Laravel will automatically resolve `Breadcrumbs::` without
// this import. This is nice for IDE syntax and refactoring.
use Diglactic\Breadcrumbs\Breadcrumbs;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('home'));
});

// Home > Blog
Breadcrumbs::for('blog', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push('Blog', route('blog'));
});

// Home > Blog > [Category]
Breadcrumbs::for('category', function (BreadcrumbTrail $trail, $category) {
    $trail->parent('blog');
    $trail->push($category->title, route('category', $category));
});
```

See the [Defining Breadcrumbs](#defining-breadcrumbs) section for more details.

### 3. Style

By default, a [Bootstrap 5 breadcrumb list](https://getbootstrap.com/docs/5.0/components/breadcrumb/) will be rendered.
To change this, initialize the config file by running this command:

```bash
php artisan vendor:publish --tag=breadcrumbs-config
```

Then, open `config/breadcrumbs.php` and edit this line:

```php
// config/breadcrumbs.php

'view' => 'breadcrumbs::bootstrap5',
```

The possible values are:

- `breadcrumbs::bootstrap5` – [Bootstrap 5](https://getbootstrap.com/docs/5.0/components/breadcrumb/)
- `breadcrumbs::bootstrap4` – [Bootstrap 4](https://getbootstrap.com/docs/4.0/components/breadcrumb/)
- `breadcrumbs::bulma` – [Bulma](https://bulma.io/documentation/components/breadcrumb/)
- `breadcrumbs::foundation6` – [Foundation 6](https://get.foundation/sites/docs/breadcrumbs.html)
- `breadcrumbs::json-ld` – [JSON-LD Structured Data](https://developers.google.com/search/docs/appearance/structured-data/breadcrumb)
- `breadcrumbs::materialize` – [Materialize](https://materializecss.com/breadcrumbs.html)
- `breadcrumbs::tailwind` – [Tailwind CSS](https://tailwindcss.com/)
- `breadcrumbs::uikit` – [UIkit](https://getuikit.com/docs/breadcrumb)
- Or, you can specify the path to a custom view, like `partials.breadcrumbs`

See the [Custom Templates](#custom-templates) section for more details.

You may also
[specify a custom view at runtime](https://github.com/diglactic/laravel-breadcrumbs#switching-views-at-runtime).

### 4. Output

Call `Breadcrumbs::render()` in the view for each page, passing it the name of the breadcrumb to use and any additional
parameters:

```blade
{{-- resources/views/home.blade.php --}}
{{ Breadcrumbs::render('home') }}

{{-- resources/views/categories/show.blade.php --}}
{{ Breadcrumbs::render('category', $category) }}
```

See the [Outputting Breadcrumbs](#outputting-breadcrumbs) section for other output options, and
see [Route-Bound Breadcrumbs](#route-bound-breadcrumbs) for a way to link breadcrumb names to route names automatically.


Defining Breadcrumbs
--------------------

Breadcrumbs will usually correspond to actions or types of page. For each breadcrumb, you specify a name, the breadcrumb
title, and the URL to link it to. Since these are likely to change dynamically, you do this in a closure, and you pass
any variables you need into the closure.

The following examples should make it clear:

### Static pages

The most simple breadcrumb is probably going to be your homepage, which will look something like this:

```php
<?php // routes/breadcrumbs.php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
     $trail->push('Home', route('home'));
});
```

For generating the URL, you can use any of the standard Laravel URL-generation methods, including:

- `url('path/to/route')` (`URL::to()`)
- `secure_url('path/to/route')`
- `route('routename')` or `route('routename', 'param')` or `route('routename', ['param1', 'param2'])` (`URL::route()`)
- ``action('controller@action')`` (``URL::action()``)
- Or just pass a string URL (`'http://www.example.com/'`)

This example would be rendered like this:

```blade
{{ Breadcrumbs::render('home') }}
```

And result in this output:

> Home

### Parent links

This is another static page, but with a parent link before it:

```php
<?php // routes/breadcrumbs.php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('blog', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push('Blog', route('blog'));
});
```

It works by calling the closure for the `home` breadcrumb defined above.

It would be rendered like this:

```blade
{{ Breadcrumbs::render('blog') }}
```

And result in this output:

> [Home](#) / Blog

Note that the default templates do not create a link for the last breadcrumb (the one for the current page), even when a
URL is specified. You can override this by creating your own template – see [Custom Templates](#custom-templates) for
more details.

### Dynamic titles and links

This is a dynamically generated page pulled from the database:

```php
<?php // routes/breadcrumbs.php

use App\Models\Post;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('post', function (BreadcrumbTrail $trail, Post $post) {
    $trail->parent('blog');
    $trail->push($post->title, route('post', $post));
});
```

The `$post` object (probably an
Eloquent [Model](https://laravel.com/api/master/Illuminate/Database/Eloquent/Model.html), but could be anything) would
be passed in from the view:

```blade
{{ Breadcrumbs::render('post', $post) }}
```

It results in this output:

> [Home](#) / [Blog](#) / Post Title

You can also chain method calls to `$trail`. If you're using
[PHP 7.4 and above with arrow function support](https://www.php.net/manual/en/functions.arrow.php), you might prefer the
following, more concise, syntax:

```php
Breadcrumbs::for(
    'post',
    fn (BreadcrumbTrail $trail, Post $post) => $trail
        ->parent('blog')
        ->push($post->title, route('post', $post))
);
```

### Nested categories

Finally, if you have nested categories or other special requirements, you can call `$trail->push()` multiple times:

```php
<?php // routes/breadcrumbs.php

use App\Models\Category;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('category', function (BreadcrumbTrail $trail, Category $category) {
    $trail->parent('blog');

    foreach ($category->ancestors as $ancestor) {
        $trail->push($ancestor->title, route('category', $ancestor));
    }

    $trail->push($category->title, route('category', $category));
});
```

Alternatively, you could make a recursive function such as this:

```php
<?php // routes/breadcrumbs.php

use App\Models\Category;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('category', function (BreadcrumbTrail $trail, Category $category) {
    if ($category->parent) {
        $trail->parent('category', $category->parent);
    } else {
        $trail->parent('blog');
    }

    $trail->push($category->title, route('category', $category->slug));
});
```

Both would be rendered like this:

```blade
{{ Breadcrumbs::render('category', $category) }}
```

And result in this:

> [Home](#) / [Blog](#) / [Grandparent Category](#) / [Parent Category](#) / Category Title

Custom Templates
----------------

### Create a view

To customize the HTML, create your own view file similar to the following:

```blade
 {{-- resources/views/partials/breadcrumbs.blade.php --}}

@unless ($breadcrumbs->isEmpty())
    <ol class="breadcrumb">
        @foreach ($breadcrumbs as $breadcrumb)

            @if (!is_null($breadcrumb->url) && !$loop->last)
                <li class="breadcrumb-item"><a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a></li>
            @else
                <li class="breadcrumb-item active">{{ $breadcrumb->title }}</li>
            @endif

        @endforeach
    </ol>
@endunless
```

If you want to work off an existing built-in template, run the following command:

```bash
php artisan vendor:publish --tag=breadcrumbs-views
```

This will copy all built-in templates into the `resources/views/vendor/breadcrumbs/` directory in your project, allowing
you to make edits directly.

#### View data

The view will receive a [Collection](https://laravel.com/api/master/Illuminate/Support/Collection.html)
called `$breadcrumbs`.

Each breadcrumb is an [object](https://www.php.net/manual/en/language.types.object.php) with the following keys:

- `title` – The breadcrumb title
- `url` – The breadcrumb URL, or `null` if none was given
- Plus additional keys for each item in `$data` (see [Custom data](#custom-data))

### Update the config

Then, update your config file with the custom view name:

```php
// config/breadcrumbs.php

'view' => 'partials.breadcrumbs', // --> resources/views/partials/breadcrumbs.blade.php
```

### Skipping the view

Alternatively, you can skip the custom view and call `Breadcrumbs::generate()` to get the breadcrumbs collection
directly:

```blade
@foreach (Breadcrumbs::generate('post', $post) as $breadcrumb)
    {{-- ... --}}
@endforeach
```

Outputting Breadcrumbs
----------------------

Call `Breadcrumbs::render()` in the view for each page, passing it the name of the breadcrumb to use and any additional
parameters.

### With Blade

```blade
{{ Breadcrumbs::render('home') }}
```

Or with a parameter:

```blade
{{ Breadcrumbs::render('category', $category) }}
```

Structured Data
---------------

To render breadcrumbs as
JSON-LD [structured data](httpshttps://developers.google.com/search/docs/appearance/structured-data/breadcrumb)
(usually for SEO reasons), use `Breadcrumbs::view()` to render the `breadcrumbs::json-ld` template in addition to the
normal one. For example:

```blade
<html>
    <head>
        ...
        {{ Breadcrumbs::view('breadcrumbs::json-ld', 'category', $category) }}
        ...
    </head>
    <body>
        ...
        {{ Breadcrumbs::render('category', $category) }}
        ...
    </body>
</html>
```

(Note: If you use [Laravel Page Speed](https://github.com/renatomarinho/laravel-page-speed) you may need to
[disable the `TrimUrls` middleware](https://github.com/renatomarinho/laravel-page-speed/issues/66).)

To specify an image, add it to the `$data` parameter in `push()`:

```php
<?php // routes/breadcrumbs.php

use App\Models\Post;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('post', function (BreadcrumbTrail $trail, Post $post) {
    $trail->parent('home');
    $trail->push($post->title, route('post', $post), ['image' => asset($post->image)]);
});
```

(If you prefer to use Microdata or RDFa you will need to create a [custom template](#custom-templates).)

Route-Bound Breadcrumbs
-----------------------

In normal usage you must call `Breadcrumbs::render($name, $params...)` to render the breadcrumbs on every page. If you
prefer, you can name your breadcrumbs the same as your routes and avoid this duplication.

### Name your routes

Make sure each of your routes has a name.

```php
<?php // routes/web.php

use Illuminate\Support\Facades\Route;

// Home
Route::name('home')->get('/', 'HomeController@index');

// Home > [Post]
Route::name('post')->get('/post/{id}', 'PostController@show');
```

For more details, see [Named Routes](https://laravel.com/docs/routing#named-routes) in the Laravel documentation.

### Name your breadcrumbs to match

For each route, create a breadcrumb with the same name and parameters. For example:

```php
<?php // routes/breadcrumbs.php

use App\Models\Post;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
     $trail->push('Home', route('home'));
});

// Home > [Post]
Breadcrumbs::for('post', function (BreadcrumbTrail $trail, Post $post) {
    $trail->parent('home');
    $trail->push($post->title, route('post', $post));
});
```

To add breadcrumbs to a [custom 404 Not Found page](https://laravel.com/docs/errors#custom-http-error-pages), use the
name `errors.404`:

```php
Breadcrumbs::for('errors.404', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push('Page Not Found');
});
```

### Output breadcrumbs in your layout

Call `Breadcrumbs::render()` with no parameters in your layout file:

```blade
{{-- resources/views/app.blade.php --}}

{{ Breadcrumbs::render() }}
```

This will automatically output breadcrumbs corresponding to the current route. The same applies
to `Breadcrumbs::generate()` and `Breadcrumbs::view()`:

### Route binding exceptions

We'll throw an `InvalidBreadcrumbException` if the breadcrumb doesn't exist, to remind you to create one. To disable
this (e.g. if you have some pages with no breadcrumbs), first initialize the config file, if you haven't already:

```bash
php artisan vendor:publish --tag=breadcrumbs-config
```

Then open the newly-created file and set this value:

```php
// config/breadcrumbs.php

'missing-route-bound-breadcrumb-exception' => false,
```

Similarly, to prevent it throwing an `UnnamedRouteException` if the current route doesn't have a name, set this value:

```php
// config/breadcrumbs.php

'unnamed-route-exception' => false,
```

### Route model binding

Laravel Breadcrumbs uses the same model binding as the controller. For example:

```php
<?php // routes/web.php

use Illuminate\Support\Facades\Route;

Route::name('post')->get('/post/{post}', 'PostController@show');
```

```php
<?php // app/Http/Controllers/PostController.php

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function show(Post $post): View // <-- Route bound model is injected here
    {
        return view('post/show', ['post' => $post]);
    }
}
```

```php
<?php // routes/breadcrumbs.php

use App\Models\Post;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('post', function (BreadcrumbTrail $trail, Post $post) { // <-- The same Post model is injected here
    $trail->parent('home');
    $trail->push($post->title, route('post', $post));
});
```

This makes your code less verbose and more efficient by only loading the post from the database once.

For more details see [Route Model Binding](https://laravel.com/docs/routing#route-model-binding) in the Laravel
documentation.

### Resourceful controllers

Laravel automatically creates route names for resourceful controllers, e.g. `photo.index`, which you can use when
defining your breadcrumbs. For example:

```php
<?php // routes/web.php

use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

Route::resource('photo', PhotoController::class);
```

```
$ php artisan route:list
+--------+----------+--------------------+---------------+-------------------------+------------+
| Domain | Method   | URI                | Name          | Action                  | Middleware |
+--------+----------+--------------------+---------------+-------------------------+------------+
|        | GET|HEAD | photo              | photo.index   | PhotoController@index   |            |
|        | GET|HEAD | photo/create       | photo.create  | PhotoController@create  |            |
|        | POST     | photo              | photo.store   | PhotoController@store   |            |
|        | GET|HEAD | photo/{photo}      | photo.show    | PhotoController@show    |            |
|        | GET|HEAD | photo/{photo}/edit | photo.edit    | PhotoController@edit    |            |
|        | PUT      | photo/{photo}      | photo.update  | PhotoController@update  |            |
|        | PATCH    | photo/{photo}      |               | PhotoController@update  |            |
|        | DELETE   | photo/{photo}      | photo.destroy | PhotoController@destroy |            |
+--------+----------+--------------------+---------------+-------------------------+------------+
```

```php
<?php // routes/breadcrumbs.php

use App\Models\Photo;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Photos
Breadcrumbs::for('photo.index', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push('Photos', route('photo.index'));
});

// Photos > Upload Photo
Breadcrumbs::for('photo.create', function (BreadcrumbTrail $trail) {
    $trail->parent('photo.index');
    $trail->push('Upload Photo', route('photo.create'));
});

// Photos > [Photo Name]
Breadcrumbs::for('photo.show', function (BreadcrumbTrail $trail, Photo $photo) {
    $trail->parent('photo.index');
    $trail->push($photo->title, route('photo.show', $photo));
});

// Photos > [Photo Name] > Edit Photo
Breadcrumbs::for('photo.edit', function (BreadcrumbTrail $trail, Photo $photo) {
    $trail->parent('photo.show', $photo);
    $trail->push('Edit Photo', route('photo.edit', $photo));
});
```

For more details see [Resource Controllers](https://laravel.com/docs/controllers#resource-controllers) in the
Laravel documentation.

(Related FAQ: [Why is there no Breadcrumbs::resource() method?](#why-is-there-no-breadcrumbsresource-method).)

Advanced Usage
--------------

### Breadcrumbs with no URL

The second parameter to `push()` is optional, so if you want a breadcrumb with no URL you can do:

```php
$trail->push('Sample');
```

In this case, `$breadcrumb->url` will be `null`.

The default Bootstrap templates provided render this with a CSS class of "active", the same as the last breadcrumb,
because otherwise they default to black text not grey which doesn't look right.

### Custom data

The `push()` method accepts an optional third parameter, `$data` – an array of arbitrary associative array of data to be
passed to the breadcrumb, which you can use in your custom template.

If you wanted each breadcrumb to have an icon, for instance, you might do:

```php
$trail->push('Home', '/', ['icon' => 'home.png']);
```

The `$data` array's entries will be merged into the breadcrumb as properties.

```blade
<li>
    <a href="{{ $breadcrumb->url }}">
        <img src="/images/icons/{{ $breadcrumb->icon }}">
        {{ $breadcrumb->title }}
    </a>
</li>
```

**Note:** do not use the keys `title` or `url`, as they will be overwritten.

### Before and after callbacks

You can register "before" and "after" callbacks to add breadcrumbs at the start/end of the trail. For example, to
automatically add the current page number at the end:

```php
<?php // routes/breadcrumbs.php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::after(function (BreadcrumbTrail $trail) {
    $page = (int) request('page', 1);

    if ($page > 1) {
        $trail->push("Page {$page}");
    }
});
```

### Getting the current page breadcrumb

To get the last breadcrumb for the current page, use `Breadcrumb::current()`. For example, you could use this to output
the current page title:

```blade
<title>{{ ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Fallback Title' }}</title>
```

To ignore a breadcrumb, add `'current' => false` to the `$data` parameter in `push()`. This can be useful to ignore
pagination breadcrumbs:

```php
<?php // routes/breadcrumbs.php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::after(function (BreadcrumbTrail $trail) {
    $page = (int) request('page', 1);

    if ($page > 1) {
        $trail->push("Page {$page}", null, ['current' => false]);
    }
});
```

```blade
<title>
    {{ ($breadcrumb = Breadcrumbs::current()) ? "$breadcrumb->title –" : '' }}
    {{ ($page = (int) request('page')) > 1 ? "Page $page –" : '' }}
    Demo App
</title>
```

For more advanced filtering, use `Breadcrumbs::generate()` and Laravel's
[Collection class](https://laravel.com/api/master/Illuminate/Support/Collection.html) methods instead:

```php
<?php

use Diglactic\Breadcrumbs\Breadcrumbs;

$current = Breadcrumbs::generate()->where('current', '!==', false)->last();
```

### Switching views at runtime

You can use `Breadcrumbs::view()` in place of `Breadcrumbs::render()` to render a template other than
the [default one](#3-style):

```blade
{{ Breadcrumbs::view('partials.breadcrumbs2', 'category', $category) }}
```

Or you can override the config setting to affect all future `render()` calls:

```php
<?php

use Illuminate\Support\Facades\Config;

Config::set('breadcrumbs.view', 'partials.breadcrumbs2');
```

```blade
{{ Breadcrumbs::render('category', $category) }}
```

Or you could call `Breadcrumbs::generate()` to get the breadcrumbs Collection and load the view manually:

```blade
@include('partials.breadcrumbs2', ['breadcrumbs' => Breadcrumbs::generate('category', $category)])
```

### Overriding the "current" route

If you call `Breadcrumbs::render()` or `Breadcrumbs::generate()` with no parameters, it will use the current route name
and parameters by default (as returned by Laravel's `Route::current()` method).

You can override this by calling `Breadcrumbs::setCurrentRoute($name, $param1, $param2...)`.

### Checking if a breadcrumb exists

To check if a breadcrumb with a given name exists, call `Breadcrumbs::exists('name')`, which returns a boolean.

### Defining breadcrumbs in a different file

If you don't want to use `routes/breadcrumbs.php`, you can change it in the config file. First initialize the config
file, if you haven't already:

```bash
php artisan vendor:publish --tag=breadcrumbs-config
```

Update this line:

```php
// config/breadcrumbs.php

'files' => base_path('routes/breadcrumbs.php'),
```

It can be an absolute path, as above, or an array:

```php
'files' => [
    base_path('breadcrumbs/admin.php'),
    base_path('breadcrumbs/frontend.php'),
],
```

So you can use `glob()` to automatically find files using a wildcard:

```php
'files' => glob(base_path('breadcrumbs/*.php')),
```

Or return an empty array `[]` to disable loading.

### Defining/using breadcrumbs in another package

If you are creating your own package, simply load your breadcrumbs file from your service provider's `boot()` method:

```php
use Illuminate\Support\ServiceProvider;

class MyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (class_exists('Breadcrumbs')) {
            require __DIR__ . '/breadcrumbs.php';
        }
    }
}
```

### Dependency injection

You can use [dependency injection](https://laravel.com/docs/providers#the-boot-method) to access the `Manager`
instance if you prefer, instead of using the `Breadcrumbs::` facade:

```php
<?php

use Diglactic\Breadcrumbs\Manager;
use Illuminate\Support\ServiceProvider;

class MyServiceProvider extends ServiceProvider
{
    public function boot(Manager $breadcrumbs): void
    {
        $breadcrumbs->for(...);
    }
}
```

### Macros

The breadcrumbs `Manager` class is [macroable](https://tighten.com/insights/the-magic-of-laravel-macros/), so you can
add your own methods. For example:

```php
<?php

use Diglactic\Breadcrumbs\Breadcrumbs;

Breadcrumbs::macro('pageTitle', function () {
    $title = ($breadcrumb = Breadcrumbs::current()) ? "{$breadcrumb->title} – " : '';

    if (($page = (int) request('page')) > 1) {
        $title .= "Page $page – ";
    }

    return "{$title} - Demo App";
});
```

```blade
<title>{{ Breadcrumbs::pageTitle() }}</title>
```

### Advanced customization

For more advanced customizations you can subclass `Breadcrumbs\Manager` and/or `Breadcrumbs\Generator`, then update the
config file with the new class name:

```php
// breadcrumbs/config.php

'manager-class' => Diglactic\Breadcrumbs\Manager::class,

'generator-class' => Diglactic\Breadcrumbs\Generator::class,
```

**Note:** configuration syntax may change between releases.


FAQ
---

### Why is there no `Breadcrumbs::resource()` method?

A few people have suggested adding `Breadcrumbs::resource()` to match
[`Route::resource()`](https://laravel.com/docs/controllers#resource-controllers), but no one has come up with a
good implementation that a) is flexible enough to deal with translations, nested resources, etc., and b) isn't overly
complex as a result.

You can always create your own using `Breadcrumbs::macro()`. Here's a good starting point:

```php
<?php // routes/breadcrumbs.php

use App\Models\SomeModel;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::macro('resource', function (string $name, string $title) {
    // Home > Blog
    Breadcrumbs::for("{$name}.index", function (BreadcrumbTrail $trail) use ($name, $title) {
        $trail->parent('home');
        $trail->push($title, route("{$name}.index"));
    });

    // Home > Blog > New
    Breadcrumbs::for("{$name}.create", function (BreadcrumbTrail $trail) use ($name) {
        $trail->parent("{$name}.index");
        $trail->push('New', route("{$name}.create"));
    });

    // Home > Blog > Post 123
    Breadcrumbs::for("{$name}.show", function (BreadcrumbTrail $trail, SomeModel $model) use ($name) {
        $trail->parent("{$name}.index");
        $trail->push($model->title, route("{$name}.show", $model));
    });

    // Home > Blog > Post 123 > Edit
    Breadcrumbs::for("{$name}.edit", function (BreadcrumbTrail $trail, SomeModel $model) use ($name) {
        $trail->parent("{$name}.show", $model);
        $trail->push('Edit', route("{$name}.edit", $model));
    });
});

Breadcrumbs::resource('blog', 'Blog');
Breadcrumbs::resource('photos', 'Photos');
Breadcrumbs::resource('users', 'Users');
```

Note that this *doesn't* deal with translations or nested resources, and it assumes that all models have a `title`
attribute (which users probably don't). Adapt it however you see fit.


Troubleshooting
---------------

#### General

- Re-read the instructions and make sure you did everything correctly.
- Start with the simple options and only use the advanced options (e.g. Route-Bound Breadcrumbs) once you understand how
  it works.

#### Class 'Breadcrumbs' not found

- Try running `composer update diglactic/laravel-breadcrumbs` to upgrade.
- Try running `php artisan package:discover` to ensure the service provider is detected by Laravel.

#### Breadcrumb not found with name ...

- Make sure you register the breadcrumbs in the right place (`routes/breadcrumbs.php` by default).
    - Try putting `dd(__FILE__)` in the file to make sure it's loaded.
    - Try putting `dd($files)` in `ServiceProvider::registerBreadcrumbs()` to check the path is correct.
    - If not, try running `php artisan config:clear` (or manually delete `bootstrap/cache/config.php`) or update the
      path in `config/breadcrumbs.php`.
- Make sure the breadcrumb name is correct.
    - If using Route-Bound Breadcrumbs, make sure it matches the route name exactly.
- To suppress these errors when using Route-Bound Breadcrumbs (if you don't want breadcrumbs on some pages), either:
    - Register them with an empty closure (no push/parent calls), or
    - Set [`missing-route-bound-breadcrumb-exception` to `false`](#route-binding-exceptions) in the config file to
      disable the check (but you won't be warned if you miss any pages).

#### ServiceProvider::registerBreadcrumbs(): Failed opening required ...

- Make sure the path is correct.
- If so, check the file ownership & permissions are correct.
- If not, try running `php artisan config:clear` (or manually delete `bootstrap/cache/config.php`) or update the path
  in `config/breadcrumbs.php`.

#### Undefined variable: breadcrumbs

- Make sure you use `{{ Breadcrumbs::render() }}` or `{{ Breadcrumbs::view() }}`, not `@include()`.

Contributing
------------

**Documentation:** If you think the documentation can be improved in any way, please do
[edit this file](https://github.com/diglactic/laravel-breadcrumbs/edit/main/README.md) and make a pull request.

**Bug fixes:** Please fix it and open a [pull request](https://github.com/diglactic/laravel-breadcrumbs/pulls).
([See below](#creating-a-pull-request) for more detailed instructions.) Bonus points if you add a unit test to make sure
it doesn't happen again!

**New features:** Only features with a clear use case and well-considered API will be accepted. They must be documented
and include unit tests. If in doubt, make a proof-of-concept (either code or documentation) and open a
[pull request](https://github.com/diglactic/laravel-breadcrumbs/pulls) to discuss the details. (Tip: If you want a
feature that's too specific to be included by default, see [Macros](#macros) or [Advanced Usage](#advanced-usage) for
ways to add them.)

### Creating a pull request

The easiest way to work on Laravel Breadcrumbs is to tell Composer to install it from source (Git) using the
`--prefer-source` flag:

```bash
rm -rf vendor/diglactic/laravel-breadcrumbs
composer install --prefer-source
```

Then checkout the main branch and create your own local branch to work on:

```bash
cd vendor/diglactic/laravel-breadcrumbs
git checkout -t origin/main
git checkout -b YOUR_BRANCH
```

Now make your changes, including unit tests and documentation (if appropriate). Run the unit tests to make sure
everything is still working:

```bash
vendor/bin/phpunit
```

Then commit the changes. [Fork the repository on GitHub](https://github.com/diglactic/laravel-breadcrumbs/fork) if you
haven't already, and push your changes to it:

```bash
git remote add YOUR_USERNAME git@github.com:YOUR_USERNAME/laravel-breadcrumbs.git
git push -u YOUR_USERNAME YOUR_BRANCH
```

Finally, browse to the repository on GitHub and create a pull request.

### Using your fork in a project

To use your own fork in a project, update the `composer.json` in your main project as follows:

```json5
{
    // ADD THIS:
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/YOUR_USERNAME/laravel-breadcrumbs.git"
        }
    ],
    "require": {
        // UPDATE THIS:
        "diglactic/laravel-breadcrumbs": "dev-YOUR_BRANCH"
    }
}
```

Replace `YOUR_USERNAME` with your GitHub username and `YOUR_BRANCH` with the branch name (e.g. `develop`). This tells
Composer to use your repository instead of the default one.

### Unit tests

To run the unit tests:

```bash
vendor/bin/phpunit
```

To run the unit tests and rebuild snapshots:

```bash
vendor/bin/phpunit -d --update-snapshots
```

To check code coverage:

```bash
vendor/bin/phpunit --coverage-html test-coverage
```

Then open `test-coverage/index.html` to view the results. Be aware of the
[edge cases](https://phpunit.de/manual/current/en/code-coverage-analysis.html#code-coverage-analysis.edge-cases) in
PHPUnit that can make it not-quite-accurate.

### New version of Laravel

The following files will need to be updated to run tests against a new Laravel version:

- [`composer.json`](composer.json)
    - `laravel/framework` (Laravel versions)
    - `php` (minimum PHP version)
    - Other dependencies (prefer bumping to the highest-available versions that work for all Laravel/PHP combinations)

- [`tests.yml`](.github/workflows/tests.yml)
    - `jobs.phpunit.strategy.matrix.laravel` (Laravel versions)
    - `jobs.phpunit.strategy.matrix.php` (PHP versions)
    - `jobs.phpunit.strategy.matrix.exclude` (Unsupported combinations)

And the following documentation, as needed:

- [`README.md`](README.md)
    - [Compatibility Chart](#compatibility-chart)

- [`UPGRADE.md`](UPGRADE.md)
    - Add new section detailing any breaking package changes

License
-------

Laravel Breadcrumbs is open-sourced software licensed under the MIT license.
