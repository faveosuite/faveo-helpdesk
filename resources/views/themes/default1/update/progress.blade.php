@extends('themes.default1.admin.layout.admin')

@section('PageHeader')
Updating Application
@stop

@section('content')

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">Updating from v{{ $currentVersion }} to v{{ $latestVersion }}</h3>
    </div>
    <div class="card-body">

        {{-- Status --}}
        <div class="text-center mb-4" id="status-section">
            <div class="spinner-border text-primary" role="status" id="status-spinner">
                <span class="visually-hidden">Working...</span>
            </div>
            <h5 class="mt-2" id="status-text">Preparing update...</h5>
        </div>

        {{-- Progress Bar --}}
        <div class="progress mb-4" style="height: 24px;" id="progress-bar-wrapper">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                 role="progressbar" style="width: 0%;" id="progress-bar">0%</div>
        </div>

        {{-- Log Output --}}
        <div class="card">
            <div class="card-header py-2">
                <strong>Update Log</strong>
            </div>
            <div class="card-body bg-dark text-white p-3" style="height: 350px; overflow-y: auto; font-family: monospace; font-size: 13px;" id="log-output">
            </div>
        </div>

        {{-- Action buttons (hidden initially) --}}
        <div class="mt-3 text-center d-none" id="done-actions">
            <a href="{{ url('file-update') }}" class="btn btn-outline-secondary me-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Updates
            </a>
            <a href="{{ url('database-update') }}" class="btn btn-outline-primary me-2">
                <i class="fa-solid fa-database me-1"></i> Check Database Update
            </a>
            <a href="{{ url('dashboard') }}" class="btn btn-primary">
                <i class="fa-solid fa-house me-1"></i> Dashboard
            </a>
        </div>

    </div>
</div>

<script>
    const csrfToken = '{{ csrf_token() }}';
    const logEl = document.getElementById('log-output');
    const progressBar = document.getElementById('progress-bar');

    function log(msg, type) {
        const colors = { info: '#6c9', error: '#f66', warn: '#fc6', success: '#6f6', muted: '#888' };
        logEl.innerHTML += '<div style="color:' + (colors[type] || '#ccc') + ';">' + msg + '</div>';
        logEl.scrollTop = logEl.scrollHeight;
    }

    function setProgress(percent, text) {
        progressBar.style.width = percent + '%';
        progressBar.textContent = percent + '%';
        if (text) document.getElementById('status-text').textContent = text;
    }

    function setDone(success) {
        document.getElementById('status-spinner').classList.add('d-none');
        document.getElementById('done-actions').classList.remove('d-none');
        if (success) {
            progressBar.classList.remove('progress-bar-animated', 'bg-info');
            progressBar.classList.add('bg-success');
        } else {
            progressBar.classList.remove('progress-bar-animated', 'bg-info');
            progressBar.classList.add('bg-danger');
        }
    }

    function startUpdate() {
        setProgress(10, 'Downloading update from GitHub...');
        log('Starting download...', 'info');

        fetch('{{ route("upgrade.download") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                log(data.message, 'success');
                setProgress(40, 'Installing update...');
                log('Extracting and applying files...', 'info');
                return installUpdate();
            } else {
                throw new Error(data.message);
            }
        })
        .catch(function(err) {
            log('Download failed: ' + err.message, 'error');
            setProgress(10, 'Update failed');
            setDone(false);
        });
    }

    function installUpdate() {
        fetch('{{ route("upgrade.apply") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                if (data.log) {
                    var total = data.log.length;
                    data.log.forEach(function(entry, idx) {
                        var type = entry.status === 'updated' ? 'success'
                                 : entry.status === 'skipped' ? 'warn'
                                 : entry.status === 'failed'  ? 'error' : 'muted';
                        log(entry.file + ' ... ' + entry.status.toUpperCase(), type);
                        setProgress(40 + Math.round((idx / total) * 55));
                    });
                }
                log('', 'info');
                log('Update complete! New version: v' + data.version, 'success');
                setProgress(100, 'Update complete!');
                setDone(true);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(function(err) {
            log('Install failed: ' + err.message, 'error');
            setProgress(40, 'Update failed');
            setDone(false);
        });
    }

    // Auto-start on page load
    startUpdate();
</script>

@stop
