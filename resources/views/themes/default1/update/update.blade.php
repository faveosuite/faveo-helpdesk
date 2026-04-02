@extends('themes.default1.admin.layout.admin')

@section('PageHeader')
Application Updates
@stop

@section('content')

<style>
    .center_align {
        justify-content: center;
    }
    .fs-30 {
        font-size: 30px;
    }
    .prl-0 {
        padding-left: 0;
        padding-right: 0;
    }
    .current_card {
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0px;
    }
    .latest_card {
        border-bottom-left-radius: 0px;
        border-top-left-radius: 0px;
    }
    .border_bottom_w {
        border-bottom-color: white !important;
        padding: .35rem !important;
    }
    .fw_500 {
        font-weight: 500 !important;
    }
    .custom-text-bg-success {
        color: #218838 !important;
    }
    .custom-bg-info {
        background-color: #17a2b8 !important;
    }
    .upd_btn {
        border: 1px solid grey !important;
        width: auto !important;
        display: inline-block !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .post {
        border-bottom: 1px solid #adb5bd;
        color: #666;
        margin-bottom: 15px;
        padding-bottom: 15px;
    }
    .post .user-block {
        margin-bottom: 15px;
        width: 100%;
    }
    .img-tag-bordered {
        border: 3px solid #adb5bd !important;
    }
    .release-tag-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 3px solid #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #6c757d;
        font-size: 14px;
    }
    .ml-3-1 {
        margin-left: 3.1rem !important;
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
            @if($updateAvailable)
                <div class="error-page">
                    <div class="error-content" style="display:flex !important;justify-content:center !important;">
                        <h3 class="custom-text-bg-success fw_500">
                            <i class="fas fa-info-circle"></i> New version available
                        </h3>
                    </div>
                </div>
            @else
                <div class="text-center">
                    <h3 class="text-muted fw_500">
                        <i class="fas fa-check-circle"></i> You are up to date
                    </h3>
                </div>
            @endif

            <br>

            @if($updateAvailable)
            {{-- Version Cards --}}
            <div class="row center_align">
                <div class="card bg-secondary text-white col-sm-3 prl-0 current_card">
                    <div class="card-header border_bottom_w">
                        <h4 class="text-center">Current Version</h4>
                    </div>
                    <div class="card-body text-center">
                        <b class="fs-30">v{{ $currentVersion }}</b>
                    </div>
                </div>
                <div class="card custom-bg-info text-white col-sm-3 prl-0 latest_card">
                    <div class="card-header border_bottom_w">
                        <h4 class="text-center">Latest Version</h4>
                    </div>
                    <div class="card-body text-center">
                        <b class="fs-30">v{{ $latestVersion }}</b>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="text-center mt-2">
                <button type="button" class="btn btn-light upd_btn" data-bs-toggle="modal" data-bs-target="#updateModal">
                    <i class="fas fa-sync"></i> Update
                </button>
                @if(count($recentReleases) > 0)
                <button type="button" class="btn btn-light upd_btn" data-bs-toggle="modal" data-bs-target="#releaseModal0">
                    <i class="fas fa-circle-info text-muted"></i> Info
                </button>
                @endif
            </div>

            {{-- Recent Versions Toggle --}}
            @if(count($recentReleases) > 0)
                <div class="text-center pt-3">
                    <p class="bg-transparent text-info text-sm cursor-pointer" data-bs-toggle="collapse" data-bs-target="#recentVersions">
                        Recent Versions <i class="fa fa-caret-down"></i>
                    </p>
                </div>
                <br>

                {{-- Version Timeline --}}
                <div class="collapse show" id="recentVersions">
                    <div class="row">
                        <div class="col-12">
                            @foreach($recentReleases as $index => $release)
                                <div class="post clearfix">
                                    <div class="d-flex align-items-start">
                                        <div class="release-tag-icon me-3">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div>
                                            <span class="username">
                                                <a href="javascript:;" class="text-info">Version v{{ $release['version'] }}</a>
                                                <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#releaseModal{{ $index }}" title="Details">
                                                    <i class="fas fa-info-circle fa-sm text-muted ms-1"></i>
                                                </a>
                                                @if($release['prerelease'])
                                                    <span class="badge bg-warning text-dark">Pre-release</span>
                                                @endif
                                            </span>
                                            <br>
                                            <span class="description text-muted" style="font-size:12.5px;">Released on - {{ \Carbon\Carbon::parse($release['published_at'])->format('F d, Y h:i a') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            @endif

        </div>
    </div>
</div>

{{-- Update Confirmation Modal --}}
@if($updateAvailable)
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="modalCloseX"></button>
            </div>
            <div class="modal-body">
                {{-- Alert area for result messages --}}
                <div class="d-none" id="update-alert-area"></div>

                {{-- Confirmation content --}}
                <div id="modal-confirm-content">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>You are about to perform an update/restore.</strong>
                        Once started, the update cannot be stopped. The backup and update may take several
                        minutes to complete. Make sure you have at least 40 MB of disk space available or
                        it may lead to Backup/Update failure.
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="backupCheck" checked>
                        <label class="form-check-label" for="backupCheck" title="{{ $backupPath }}">
                            Take System Backup before Update (recommended)
                        </label>
                    </div>
                </div>

                {{-- Updating spinner content --}}
                <div class="d-none text-center py-4" id="modal-updating-content">
                    <div class="spinner-border text-primary mb-3" role="status" style="width:3rem;height:3rem;">
                        <span class="visually-hidden">Working...</span>
                    </div>
                    <p class="mb-0" id="updating-text">Maintenance Mode Enabled... File system Updating.. Do not close the window.</p>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="modalCloseBtn">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="continueBtn" onclick="startUpdate()">
                    <i class="fas fa-arrow-right me-1"></i> Continue
                </button>
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
                    <i class="fa fa-external-link me-1"></i> GitHub
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<script>
var csrfToken = '{{ csrf_token() }}';
var headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' };

function startUpdate() {
    document.getElementById('modal-confirm-content').classList.add('d-none');
    document.getElementById('modal-updating-content').classList.remove('d-none');
    document.getElementById('continueBtn').classList.add('d-none');
    document.getElementById('modalCloseBtn').classList.add('d-none');
    document.getElementById('modalCloseX').classList.add('d-none');

    var takeBackup = document.getElementById('backupCheck').checked;

    if (takeBackup) {
        doBackup();
    } else {
        doDownload();
    }
}

function doBackup() {
    document.getElementById('updating-text').textContent = 'Taking system backup... Please wait.';

    fetch('{{ route("upgrade.backup") }}', {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ path: '{{ $backupPath }}', autoUpdate: true }),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showResult('success', data.message || 'Backup and update started.');
        } else {
            throw new Error(data.message);
        }
    })
    .catch(function(err) {
        showResult('error', 'Backup failed: ' + err.message);
    });
}

function doDownload() {
    document.getElementById('updating-text').textContent = 'Maintenance Mode Enabled... File system Updating.. Do not close the window.';

    fetch('{{ route("upgrade.download") }}', {
        method: 'POST',
        headers: headers,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            return installUpdate();
        } else {
            throw new Error(data.message);
        }
    })
    .catch(function(err) {
        showResult('error', err.message);
    });
}

function installUpdate() {
    fetch('{{ route("upgrade.apply") }}', {
        method: 'POST',
        headers: headers,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            return syncDatabase(data.data.version);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(function(err) {
        showResult('error', err.message);
    });
}

function syncDatabase(version) {
    document.getElementById('updating-text').textContent = 'Maintenance Mode Enabled... Database Updating.. Do not close the window.';

    fetch('{{ route("upgrade.database") }}', {
        method: 'POST',
        headers: headers,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showResult('success', 'Update complete! New version: v' + version);
        } else {
            showResult('warning', 'Files updated to v' + version + ' but database sync failed: ' + data.message);
        }
    })
    .catch(function(err) {
        showResult('warning', 'Files updated to v' + version + ' but database sync failed: ' + err.message);
    });
}

function showResult(type, msg) {
    document.getElementById('modal-updating-content').classList.add('d-none');
    document.getElementById('modal-confirm-content').classList.add('d-none');

    var alertArea = document.getElementById('update-alert-area');
    var alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
    var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    alertArea.innerHTML = '<div class="alert ' + alertClass + ' mb-3"><i class="fas ' + icon + ' me-1"></i> ' + msg + '</div>';
    alertArea.classList.remove('d-none');

    document.getElementById('modalCloseBtn').classList.remove('d-none');
    document.getElementById('modalCloseX').classList.remove('d-none');

    setTimeout(function() {
        window.location.href = '{{ url("file-update") }}';
    }, 3000);
}
</script>

@stop
