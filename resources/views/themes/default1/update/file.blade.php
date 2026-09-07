@extends('themes.default1.layouts.login')

@section('body')

    <div class="row justify-content-center">
        <div class="col-12">

            {{-- Success Alert --}}
            @if(Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ Session::get('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Failure Alert --}}
            @if(Session::has('fails'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-ban-fill me-2"></i>
                    <strong>{!! Lang::get('lang.alert') !!}!</strong>
                    {{ Session::get('fails') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title mb-3">
                        <i class="bi bi-arrow-repeat me-2 text-primary"></i>
                        File Update Required
                    </h3>
                    <p class="card-text">
                        <strong>{{ ucfirst(Config::get('app.name')) }}</strong> has been updated! Before we send you on your own way,
                        we have to update your files to the newest version.
                    </p>
                    <p class="card-text text-muted">
                        <i class="bi bi-hourglass-split me-1"></i>
                        The update process may take a little while, so please be patient.
                    </p>
                    <a href="{{ $url }}" class="btn btn-primary">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>
                        Update {{ ucfirst(Config::get('app.name')) }} Files
                    </a>
                </div>
            </div>

        </div>
    </div>

@stop