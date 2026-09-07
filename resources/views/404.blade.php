@extends('themes.default1.layouts.installer')
@section('content')
<div class="app-main">
    <!-- Content Header (Page header) -->
    <section class="app-content-header">
        <h1>
            404 Error Page
        </h1>
    </section>

    <!-- Main content -->
    <section class="app-content">

        <div class="error-page">
            <h2 class="headline text-yellow"> 404</h2>
            <div class="error-content">
                <h3><i class="fa-solid fa-warning text-yellow"></i> Oops! Page not found.</h3>
                <p>
                    We could not find the page you were looking for.
                </p>
            </div><!-- /.error-content -->
        </div><!-- /.error-page -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@stop