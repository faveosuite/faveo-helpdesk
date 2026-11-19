{{--
    Live password checklist under a password field (same look as the installer).

    @include('partials.password-rules', ['input' => 'the-input-id'])

    Rules come from App\Rules\StrongPassword, which the server validates with too.
    From the page's own submit handler call:
        FaveoPasswordRules.check('the-input-id')  // true when valid; shows the list
--}}
@php
    $passwordRules = [];
    foreach (\App\Rules\StrongPassword::requirements() as $key => $requirement) {
        $passwordRules[] = ['key' => $key, 'pattern' => $requirement['pattern']];
    }
@endphp
<div class="password-rules mt-2" id="{{ $input }}-rules" style="display: none;">
    <div class="fw-bold" style="font-weight: bold;">{{ Lang::get('lang.password_must_include') }}</div>
    <ul class="mb-0" style="padding-left: 20px; margin: 4px 0 0; list-style: disc;">
        @foreach(\App\Rules\StrongPassword::requirements() as $key => $requirement)
            <li data-rule="{{ $key }}" style="color: red;">{{ \App\Rules\StrongPassword::label($key) }}</li>
        @endforeach
    </ul>
</div>
<script>
    window.FaveoPasswordRules = window.FaveoPasswordRules || (function () {
        var config = {};

        function evaluate(id) {
            var input = document.getElementById(id);
            var box = document.getElementById(id + '-rules');
            if (!input || !box || !config[id]) {
                return true;
            }
            var value = input.value;
            var length = Array.from(value).length;
            var allValid = true;
            config[id].rules.forEach(function (rule) {
                var ok = rule.pattern === null
                    ? length >= config[id].min && length <= config[id].max
                    : new RegExp(rule.pattern).test(value);
                var item = box.querySelector('[data-rule="' + rule.key + '"]');
                if (item) {
                    item.style.color = ok ? 'green' : 'red';
                }
                if (!ok) {
                    allValid = false;
                }
            });
            box.style.display = '';
            input.classList.toggle('is-invalid', !allValid);
            input.style.borderColor = allValid ? '' : 'red';
            return allValid;
        }

        return {
            register: function (id, rules, min, max) {
                config[id] = {rules: rules, min: min, max: max};
                var input = document.getElementById(id);
                if (input) {
                    input.addEventListener('input', function () { evaluate(id); });
                }
            },
            check: evaluate,
            reset: function (id) {
                var input = document.getElementById(id);
                var box = document.getElementById(id + '-rules');
                if (box) {
                    box.style.display = 'none';
                }
                if (input) {
                    input.classList.remove('is-invalid');
                    input.style.borderColor = '';
                }
            }
        };
    })();
    FaveoPasswordRules.register(@json($input), @json($passwordRules), {{ \App\Rules\StrongPassword::MIN }}, {{ \App\Rules\StrongPassword::MAX }});
</script>
