{{-- "Add category" pop-up used on the Add/Edit article pages.

     It saves over AJAX so the article page is never reloaded: whatever the agent
     already typed into the article's Name / Description stays in place. On success
     the new category is appended to the article's category list (#article-category-list)
     and selected. Validation errors are shown inside the pop-up.

     The fields deliberately do not use html()->text('name') etc.: those bind to
     old('name') and would otherwise be pre-filled with the article's own values
     after an article validation error (both forms use name/description). --}}
<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#add-category-modal">{{ Lang::get('lang.addcategory') }}</button>

<div class="modal fade" id="add-category-modal" tabindex="-1" aria-labelledby="add-category-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-category-form" action="{{ route('category.store') }}" method="POST" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="add-category-title">{{ Lang::get('lang.addcategory') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ Lang::get('lang.close') }}"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="add-category-error" role="alert"></div>

                    <div class="row">
                        <div class="col-sm-7 mb-3">
                            <label for="add-category-name" class="form-label">{{ Lang::get('lang.name') }}<span class="text-red"> *</span></label>
                            <input type="text" name="name" id="add-category-name" class="form-control" maxlength="250" autocomplete="off">
                            <div class="invalid-feedback" data-error-for="name"></div>
                        </div>

                        <div class="col-sm-5 mb-3">
                            <label class="form-label d-block">{{ Lang::get('lang.status') }}</label>
                            <label class="fw-normal me-3">
                                <input type="radio" name="status" value="1" id="add-category-status-active" checked> {{ Lang::get('lang.active') }}
                            </label>
                            <label class="fw-normal me-3">
                                <input type="radio" name="status" value="0" id="add-category-status-inactive"> {{ Lang::get('lang.inactive') }}
                            </label>
                        </div>

                        <div class="col-sm-12 mb-3">
                            <label for="add-category-description" class="form-label">{{ Lang::get('lang.description') }}<span class="text-red"> *</span></label>
                            <textarea name="description" id="add-category-description" class="form-control" rows="6" placeholder="{{ Lang::get('lang.enter_the_description') }}"></textarea>
                            <div class="invalid-feedback" data-error-for="description"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ Lang::get('lang.close') }}</button>
                    <button type="submit" class="btn btn-primary" id="add-category-submit">{{ Lang::get('lang.add') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
$(function () {
    var $modal  = $('#add-category-modal');
    var $form   = $('#add-category-form');
    var $submit = $('#add-category-submit');
    var $alert  = $('#add-category-error');

    function clearErrors() {
        $alert.addClass('d-none').empty();
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('[data-error-for]').empty();
    }

    function resetForm() {
        clearErrors();
        $form[0].reset();
    }

    $modal.on('shown.bs.modal', function () {
        $('#add-category-name').trigger('focus');
    });
    $modal.on('hidden.bs.modal', resetForm);

    $form.on('submit', function (e) {
        e.preventDefault();
        clearErrors();
        $submit.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: { 'Accept': 'application/json' }
        }).done(function (res) {
            var cat = res.category;

            // Add the new category to the article's list and select it.
            var $input = $('<input>', { type: 'radio', name: 'category_id[]', id: 'category-' + cat.id, value: cat.id, checked: true });
            var $label = $('<label>', { 'class': 'fw-normal' }).append($input, document.createTextNode(' ' + cat.name));
            $('#article-category-list').append($('<div>', { 'class': 'mb-1' }).append($label));
            $('#no-category-warning').remove();

            // Show the confirmation above the article form without touching its fields.
            var $ok = $('<div>', { 'class': 'alert alert-success alert-dismissible', role: 'alert' })
                .append($('<i>', { 'class': 'fa-solid fa-circle-check me-1' }), document.createTextNode(res.message))
                .append($('<button>', { type: 'button', 'class': 'btn-close', 'data-bs-dismiss': 'alert', 'aria-label': 'Close' }));
            $('#article-category-flash').empty().append($ok);

            bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
        }).fail(function (xhr) {
            var errors = xhr.responseJSON && xhr.responseJSON.errors;
            if (xhr.status === 422 && errors) {
                $.each(errors, function (field, messages) {
                    var $field = $form.find('[name="' + field + '"]');
                    if ($field.length && $form.find('[data-error-for="' + field + '"]').length) {
                        $field.addClass('is-invalid');
                        $form.find('[data-error-for="' + field + '"]').text(messages[0]);
                    } else {
                        $alert.removeClass('d-none').append($('<div>').text(messages[0]));
                    }
                });
            } else {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || @json(Lang::get('lang.category_not_inserted'));
                $alert.removeClass('d-none').text(msg);
            }
        }).always(function () {
            $submit.prop('disabled', false);
        });
    });
});
</script>
