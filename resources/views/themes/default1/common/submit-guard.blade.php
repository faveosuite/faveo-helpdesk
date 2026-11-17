{{--
    Global duplicate-submission guard. Included once by every layout, after jQuery.
    Behaviour and opt-outs are documented at the top of lb-faveo/js/submit-guard.js.
--}}
<script>window.faveoSubmitGuardConfig = { text: @json(Lang::get('lang.please_wait')) };</script>
<script src="{{ assetLink('js', 'submit-guard') }}" type="text/javascript"></script>
