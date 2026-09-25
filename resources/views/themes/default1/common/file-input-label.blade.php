{{--
    Translated stand-in for the browser's own "Choose file / No file chosen" text,
    which the browser draws in its UI language and the page cannot translate.

    Give the file input the class "visually-hidden" and include this right after it:
        @include('themes.default1.common.file-input-label', ['for' => 'attachment', 'multiple' => true])
--}}
@php($multiple = $multiple ?? false)
<span class="d-inline-flex align-items-center gap-2 flex-wrap">
    <label for="{{ $for }}" class="btn btn-outline-secondary btn-sm mb-0">
        <i class="fa-solid fa-paperclip"></i> {{ Lang::get($multiple ? 'lang.choose_files' : 'lang.choose_file') }}
    </label>
    <span class="text-muted text-break" data-file-status-for="{{ $for }}">{{ Lang::get('lang.no_file_chosen') }}</span>
</span>

@once
<script>
    // One delegated listener for every input that uses this partial: shows the picked
    // file name (or the count for several) next to the translated button.
    document.addEventListener('change', function (event) {
        var input = event.target;
        if (!input || input.type !== 'file' || !input.id) {
            return;
        }
        var status = document.querySelector('[data-file-status-for="' + input.id + '"]');
        if (!status) {
            return;
        }
        var files = input.files || [];
        if (files.length === 0) {
            status.textContent = @json(Lang::get('lang.no_file_chosen'));
        } else if (files.length === 1) {
            status.textContent = files[0].name;
        } else {
            status.textContent = @json(Lang::get('lang.files_chosen')).replace(':count', files.length);
        }
    });
</script>
@endonce
