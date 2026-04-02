@extends('themes.default1.admin.layout.admin')

@section('PageHeader')
Application Updates
@stop

@section('content')

<style>
    .version-cards {
        display: flex;
        justify-content: center;
        align-items: stretch;
        margin: 0 auto;
        max-width: 480px;
    }
    .version-cards .card {
        flex: 1;
        margin-bottom: 0;
        border: none;
    }
    .version-cards .card:first-child {
        border-radius: 8px 0 0 8px;
    }
    .version-cards .card:last-child {
        border-radius: 0 8px 8px 0;
    }
    .version-cards .card-header {
        border-bottom: 1px solid rgba(255,255,255,0.2) !important;
        padding: 10px;
    }
    .version-cards .card-header h5 {
        font-size: 14px;
        font-weight: 400;
        margin: 0;
    }
    .version-cards .card-body {
        padding: 15px 10px;
    }
    .version-cards .card-body b {
        font-size: 26px;
    }
    .current-card {
        background-color: #495a6e !important;
    }
    .latest-card {
        background-color: #17a2b8 !important;
    }
    .fw_500 {
        font-weight: 500;
    }
    .upd_btn {
        margin: 0 4px;
        border: 1px solid #dee2e6;
        padding: 4px 14px;
        font-size: 14px;
    }
    .upd_btn:hover {
        background-color: #e9ecef;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .release-item {
        display: flex;
        align-items: flex-start;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .release-item:last-child {
        border-bottom: none;
    }
    .release-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 12px;
        margin-top: 2px;
        color: #6c757d;
        font-size: 14px;
    }
    .release-info {
        flex: 1;
        min-width: 0;
    }
    .release-info .release-title {
        font-size: 14px;
    }
    .release-info .release-title a {
        color: #17a2b8;
        text-decoration: none;
        font-weight: 500;
    }
    .release-info .release-date {
        font-size: 12.5px;
        color: #888;
    }
    .release-info .release-body {
        font-size: 13px;
        color: #666;
        margin-top: 4px;
        line-height: 1.4;
    }
</style>

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">Application Updates</h3>
        <div class="card-tools"></div>
    </div>
    <div class="card-body">
        <div>

            {{-- Status Message --}}
            <div class="text-center">
                @if($updateAvailable)
                    <h3 class="text-success fw_500">
                        <i class="fas fa-info-circle"></i> New version available
                    </h3>
                @else
                    <h3 class="text-muted fw_500">
                        <i class="fas fa-check-circle"></i> You are up to date
                    </h3>
                @endif
            </div>

            <br>

            {{-- Version Cards --}}
            <div class="version-cards">
                <div class="card current-card text-white">
                    <div class="card-header">
                        <h5 class="text-center">Current Version</h5>
                    </div>
                    <div class="card-body text-center">
                        <b>v{{ $currentVersion }}</b>
                    </div>
                </div>
                <div class="card latest-card text-white">
                    <div class="card-header">
                        <h5 class="text-center">Latest Version</h5>
                    </div>
                    <div class="card-body text-center">
                        <b>v{{ $latestVersion }}</b>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="text-center mt-3">
                @if($updateAvailable)
                    <button type="button" class="btn btn-light upd_btn" data-bs-toggle="modal" data-bs-target="#updateModal">
                        <i class="fas fa-sync"></i> Update
                    </button>
                @endif
                @if(count($recentReleases) > 0)
                    <button type="button" class="btn btn-light upd_btn" data-bs-toggle="modal" data-bs-target="#releaseModal0">
                        <i class="fas fa-circle-info text-muted"></i> Info
                    </button>
                @endif
            </div>

            {{-- Recent Versions Toggle --}}
            @if(count($recentReleases) > 0)
                <div class="text-center pt-3">
                    <p class="text-info text-sm cursor-pointer mb-0" data-bs-toggle="collapse" data-bs-target="#recentVersions">
                        Recent Versions <i class="fa fa-caret-down"></i>
                    </p>
                </div>

                <br>

                {{-- Version Timeline --}}
                <div class="collapse show" id="recentVersions">
                    @foreach($recentReleases as $index => $release)
                        <div class="release-item">
                            <div class="release-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="release-info">
                                <div class="release-title">
                                    <a href="{{ $release['html_url'] }}" target="_blank">Version v{{ $release['version'] }}</a>
                                    <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#releaseModal{{ $index }}" title="Details">
                                        <i class="fas fa-info-circle fa-sm text-muted ms-1"></i>
                                    </a>
                                    @if($release['prerelease'])
                                        <span class="badge bg-warning text-dark ms-1">Pre-release</span>
                                    @endif
                                </div>
                                <div class="release-date">Released on - {{ \Carbon\Carbon::parse($release['published_at'])->format('F d, Y h:i a') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Update Confirmation Modal --}}
@if($updateAvailable)
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>You are about to perform an update/restore.</strong>
                    Once started, the update cannot be stopped. The backup and update may take several
                    minutes to complete. Make sure you have at least 40 MB of disk space available or
                    it may lead to Backup/Update failure.
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="backupCheck" checked>
                    <label class="form-check-label" for="backupCheck">
                        Take System Backup before Update (recommended)
                    </label>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <a href="{{ url('file-upgrade') }}" class="btn btn-primary" id="continueBtn">
                    <i class="fas fa-arrow-right me-1"></i> Continue
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Release Detail Modals --}}
@foreach($recentReleases as $index => $release)
<div class="modal fade" id="releaseModal{{ $index }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ucfirst(config('app.name')) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-borderless mb-3">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Version</td>
                        <td class="fw-bold">v{{ $release['version'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Released on</td>
                        <td class="fw-bold">{{ \Carbon\Carbon::parse($release['published_at'])->format('F d, Y h:i a') }}</td>
                    </tr>
                    @if($release['prerelease'])
                    <tr>
                        <td class="text-muted">Type</td>
                        <td><span class="badge bg-warning text-dark">Pre-release</span></td>
                    </tr>
                    @endif
                </table>

                @if(!empty($release['body']))
                    <h6 class="fw-bold mb-2">Release Notes</h6>
                    <div class="border rounded p-3 bg-light" style="max-height: 350px; overflow-y: auto;">
                        {!! \Illuminate\Support\Str::markdown($release['body']) !!}
                    </div>
                @else
                    <p class="text-muted">No release notes available.</p>
                @endif
            </div>
            <div class="modal-footer">
                <a href="{{ $release['html_url'] }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="fab fa-github me-1"></i> GitHub
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach


@stop
