{{-- In-app confirmation dialog, replacing the browser's native confirm() box.

     Put data-confirm="message" on a submit button or a link. Clicking it opens
     this modal; the form is submitted / the link is followed only after the user
     confirms. Optional: data-confirm-title, data-confirm-button (label of the
     confirming button). Works for markup added later (DataTables rows).

     For a plain message in place of the browser's alert():
     FaveoDialog.alert(message, { title: '...', onClose: function () {} }); --}}
<div class="modal fade" id="faveo-confirm-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="faveo-confirm-title"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="faveo-confirm-message"></div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" id="faveo-confirm-cancel" data-bs-dismiss="modal">{{ Lang::get('lang.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="faveo-confirm-ok"></button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var modalEl = document.getElementById('faveo-confirm-modal');
    if (!modalEl || !window.bootstrap || !bootstrap.Modal) {
        return;
    }
    var defaults = {
        title: @json(Lang::get('lang.confirm_deletion')),
        button: @json(Lang::get('lang.delete'))
    };
    var okButton = document.getElementById('faveo-confirm-ok');
    var cancelButton = document.getElementById('faveo-confirm-cancel');
    var footer = okButton.parentNode;
    var pending = null;
    var onClose = null;

    // message: true = one OK button (alert), false = Cancel + confirm button
    function open(title, message, buttonLabel, isMessage) {
        document.getElementById('faveo-confirm-title').textContent = title;
        document.getElementById('faveo-confirm-message').textContent = message;
        okButton.textContent = buttonLabel;
        okButton.className = 'btn ' + (isMessage ? 'btn-primary' : 'btn-danger');
        cancelButton.style.display = isMessage ? 'none' : '';
        footer.className = 'modal-footer ' + (isMessage ? 'justify-content-end' : 'justify-content-between');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    window.FaveoDialog = {
        alert: function (message, options) {
            options = options || {};
            pending = null;
            onClose = options.onClose || null;
            open(options.title || @json(Lang::get('lang.alert')), message, @json(Lang::get('lang.ok')), true);
        }
    };

    document.addEventListener('click', function (event) {
        var el = event.target.closest ? event.target.closest('[data-confirm]') : null;
        if (!el || el.disabled || el.classList.contains('disabled')) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        pending = el;
        onClose = null;
        open(el.getAttribute('data-confirm-title') || defaults.title, el.getAttribute('data-confirm'),
            el.getAttribute('data-confirm-button') || defaults.button, false);
    }, true);

    okButton.addEventListener('click', function () {
        var el = pending;
        pending = null;
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        if (!el) {
            return;
        }
        if (el.form) {
            if (el.form.requestSubmit) {
                el.form.requestSubmit(el.type === 'submit' ? el : undefined);
            } else {
                el.form.submit();
            }
        } else if (el.href) {
            window.location.href = el.href;
        }
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        var callback = onClose;
        pending = null;
        onClose = null;
        if (callback) {
            callback();
        }
    });
})();
</script>
