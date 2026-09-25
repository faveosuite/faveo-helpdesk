@extends('themes.default1.installer.layout.installer')

@section('license')
done
@stop

@section('environment')
done
@stop

@section('database')
done
@stop

@section('locale')
active
@stop

@section('content')
<style>
    /* style.css pushes every "td input/select" right with margin-left:250px; reset it for this table */
    #getting-started-table { border-collapse: collapse; }
    #getting-started-table td { vertical-align: middle; padding: 4px 0; }
    #getting-started-table td.gs-label { width: 430px; }
    #getting-started-table td.gs-help { width: 30px; padding-left: 8px; }
    #getting-started-table td.gs-help i { float: none; padding: 0; }
    #getting-started-table td.gs-section { padding-top: 20px; }
    #getting-started-table td.gs-section h1 { border-bottom: 0; margin: 0; }
    #getting-started-table td input,
    #getting-started-table td select { margin-left: 0; width: 295px; box-sizing: border-box; padding: 3px; }
    #password-rules-label { font-size: 14px; color: #666; padding-top: 4px; }
    #password-rules { list-style: disc; margin: 0; padding: 0 0 0 20px; }
    #password-rules li { font-size: 14px; padding-bottom: 0; }
    #confirm-password-error { font-size: 14px; color: red; }
</style>
<div id="form-content">
<div ng-app="myApp">
        <h1 style="text-align: center; border-bottom: 0;">Getting Started</h1>
        {!! html()->form('POST', route('postaccount'))->attributes(['id' => 'postaccount'])->open() !!}

        <!-- checking if the form submit fails -->
        @if($errors->first('firstname')||$errors->first('Lastname')||$errors->first('email')||$errors->first('username')||$errors->first('password')||$errors->first('confirmpassword'))
            <div class="woocommerce-message woocommerce-tracker">
                <div class="fail">
                    @foreach(['firstname', 'Lastname', 'email', 'username', 'password', 'confirmpassword'] as $field)
                        @if($errors->first($field))
                            <span id="fail">{!! $errors->first($field, ':message') !!}</span><br/>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <!-- checking if the system fails -->
        @if(Session::has('fails'))
            <div class="woocommerce-message woocommerce-tracker">
                <div class="fail">
                    <span id="fail">{{Session::get('fails')}} </span><br/><br/>
                </div>
            </div>
        @endif

    <div ng-controller="MainController">
        <table id="getting-started-table">
            <tr>
                <td colspan="3" class="gs-section"><h1>Sign up as Admin</h1></td>
            </tr>
            <tr>
                <td class="gs-label">
                    <label for="firstname">First Name<span style="color: red;font-size:12px;">*</span></label>
                </td>
                <td>
                    {!! html()->text('firstname', null)->id('firstname')->required() !!}
                </td>
                <td class="gs-help"></td>
            </tr>
            <tr>
                <td class="gs-label">
                    <label for="Lastname">Last Name<span style="color: red;font-size:12px;">*</span></label>
                </td>
                <td>
                    {!! html()->text('Lastname', null)->id('Lastname')->required() !!}
                </td>
                <td class="gs-help"></td>
            </tr>
            <tr>
                <td class="gs-label">
                    <label for="email">Email<span style="color: red;font-size:12px;">*</span></label>
                </td>
                <td>
                    {!! html()->email('email', null)->id('email')->required() !!}
                </td>
                <td class="gs-help"></td>
            </tr>
            <tr>
                <td class="gs-label">
                    <label for="username">Username<span style="color: red;font-size:12px;">*</span></label>
                </td>
                <td>
                    {!! html()->text('username', null)->id('username')->required() !!}
                </td>
                <td class="gs-help">
                    <button type="button" data-bs-toggle="popover" tabIndex="-1" data-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{UserNametitle}}" data-content="@{{UserNamecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa fa-question-circle" style="padding: 0px;"></i>
                    </button>
                </td>
            </tr>
            <tr>
                <td class="gs-label" style="vertical-align: top; padding-top: 6px;">
                    <label for="password">Password<span style="color: red;font-size:12px;">*</span></label>
                </td>
                <td>
                    <input type="password" name="password" id="password" required="true">
                    <div id="password-rules-label"></div>
                    <ul id="password-rules"></ul>
                </td>
                <td class="gs-help"></td>
            </tr>
            <tr>
                <td class="gs-label">
                    <label for="confirmpassword">Confirm Password<span style="color: red;font-size:12px;">*</span></label>
                </td>
                <td>
                    <input type="password" name="confirmpassword" id="confirmpassword" required="true">
                    <div id="confirm-password-error"></div>
                </td>
                <td class="gs-help"></td>
            </tr>

            <tr>
                <td colspan="3" class="gs-section"><h1>System Information</h1></td>
            </tr>
            <tr>
                <td class="gs-label">
                    {!! html()->label(Lang::get('lang.date_time'), 'datetime') !!}
                </td>
                <td>
                    <select name="datetime" id="datetime" data-placeholder="Choose a date format..." class="chosen-select" style="width:295px;">
                        <option value="d/m/Y H:i:s" @if(old('datetime') == 'd/m/Y H:i:s') selected @endif>DD/MM/YYYY H:i:s</option>
                        <option value="m/d/Y H:i:s" @if(old('datetime') == 'm/d/Y H:i:s') selected @endif>MM/DD/YYYY H:i:s</option>
                        <option value="Y/m/d H:i:s" @if(old('datetime') == 'Y/m/d H:i:s') selected @endif>YYYY/MM/DD H:i:s</option>
                    </select>
                </td>
                <td class="gs-help">
                    <button type="button" data-bs-toggle="popover" tabIndex="-1" data-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Datetimetitle}}" data-content="@{{Datetimecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa fa-question-circle" style="padding: 0px;"></i>
                    </button>
                </td>
            </tr>
            <?php
                $display = [];
                $timezonesList = \App\Model\helpdesk\Utility\Timezones::orderBy('name', 'ASC')->get();
                foreach ($timezonesList as $timezone) {
                    $location = $timezone->location;
                    $start = strpos($location, '(');
                    $end = strpos($location, ')', $start + 1);
                    $result = substr($location, $start + 1, $end - $start - 1);
                    $display[] = ['id' => $timezone->name, 'name' => '('.$result.')'.' '.$timezone->name];
                }
                $timezones = array_column($display, 'name', 'id');
            ?>
            <tr>
                <td class="gs-label">
                    {!! html()->label(Lang::get('lang.time_zone'), 'timezone') !!}
                </td>
                <td>
                    <select name="timezone" id="timezone" data-placeholder="Choose a timezone..." class="chosen-select" style="width:295px;" required>
                        @foreach($timezones as $key => $value)
                            <option value="{{ $key }}" @if(old('timezone') == $key) selected @endif>{{ $value }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="gs-help">
                    <button type="button" data-bs-toggle="popover" tabIndex="-1" data-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Timezonetitle}}" data-content="@{{Timezonecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa fa-question-circle" style="padding: 0px;"></i>
                    </button>
                </td>
            </tr>
            <?php
                $values = array_slice(scandir(base_path('lang')), 2);
                $selectedLanguage = old('language', 'en');
            ?>
            <tr>
                <td class="gs-label">
                    {!! html()->label(Lang::get('lang.language'), 'language') !!}
                </td>
                <td>
                    <select name="language" id="language" data-placeholder="Choose a language..." class="chosen-select" style="width:295px;">
                        @foreach($values as $value)
                            @if(Config::get('languages.' . $value))
                                <option value="{{ $value }}" @if($value == $selectedLanguage) selected @endif>{!! Config::get('languages.' . $value)[0] !!}&nbsp;({!! Config::get('languages.' . $value)[1] !!})</option>
                            @endif
                        @endforeach
                    </select>
                </td>
                <td class="gs-help">
                    <button type="button" data-bs-toggle="popover" tabIndex="-1" data-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Languagetitle}}" data-content="@{{Languagecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa fa-question-circle" style="padding: 0px;"></i>
                    </button>
                </td>
            </tr>
        </table>
        <br><br>
        <p class="setup-actions step" style="overflow: hidden; padding-bottom: 50px;">
            <input type="submit" id="submitme" class="button-primary button button-large button-next" value="Install">
        </p>
        {!! html()->form()->close() !!}
    </div>
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.0/angular.min.js"></script>
    <script src="{{asset("lb-faveo/js/angular2.js")}}" type="text/javascript"></script>
</div>
</div>
    <script type="text/javascript">
        // Preselect the browser's timezone (runs before the layout initialises chosen).
        @if(!old('timezone'))
        (function () {
            try {
                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                // Browsers may report legacy IANA names; map them to the names stored in the timezones table.
                var aliases = {
                    'Asia/Calcutta': 'Asia/Kolkata', 'Asia/Saigon': 'Asia/Ho_Chi_Minh', 'Asia/Katmandu': 'Asia/Kathmandu',
                    'Asia/Rangoon': 'Asia/Yangon', 'Europe/Kiev': 'Europe/Kyiv', 'America/Buenos_Aires': 'America/Argentina/Buenos_Aires'
                };
                var select = document.getElementById('timezone');
                if (tz && select) {
                    var candidates = [tz, aliases[tz]];
                    for (var i = 0; i < candidates.length; i++) {
                        if (candidates[i] && select.querySelector('option[value="' + candidates[i] + '"]')) {
                            select.value = candidates[i];
                            break;
                        }
                    }
                }
            } catch (e) {}
        })();
        @endif

        var requiredFields = ['firstname', 'Lastname', 'email', 'username', 'password', 'confirmpassword'];

        var passwordRequirements = [
            { regex: /^.{9,16}$/, message: 'Between 9-16 characters' },
            { regex: /[a-z]/, message: 'One lowercase letter (a-z)' },
            { regex: /[A-Z]/, message: 'One uppercase letter (A-Z)' },
            { regex: /[0-9]/, message: 'One number (0-9)' },
            { regex: /[~!@#$%^&*()_+|;:,.<>?]/, message: 'One special character (@, #, $, etc.)' }
        ];

        function validatePassword() {
            var value = $('#password').val();
            var $rules = $('#password-rules').empty();
            var allValid = true;
            $('#password-rules-label').text('Passwords must include at least:');
            $.each(passwordRequirements, function (i, req) {
                var isValid = req.regex.test(value);
                $('<li>').text(req.message).css('color', isValid ? 'green' : 'red').appendTo($rules);
                if (!isValid) allValid = false;
            });
            return allValid;
        }

        function validateConfirmPassword() {
            var matches = $('#password').val() === $('#confirmpassword').val();
            $('#confirm-password-error').text(matches ? '' : 'Passwords do not match');
            return matches;
        }

        $('#password').on('input', function () {
            validatePassword();
            if ($('#confirmpassword').val()) validateConfirmPassword();
        });
        $('#confirmpassword').on('input', validateConfirmPassword);

        @foreach(['firstname', 'Lastname', 'email', 'username', 'password', 'confirmpassword'] as $field)
            @if($errors->has($field))
                addErrorClass('{{ $field }}');
            @endif
        @endforeach

        $('#postaccount').on('submit', function(e) {
            var emptyField = false;
            $.each(requiredFields, function (i, name) {
                if ($('[name="' + name + '"]').val() == '') {
                    addErrorClass(name);
                    emptyField = true;
                }
            });
            if (emptyField) {
                alert('Please fill all required values.');
                e.preventDefault();
                return;
            }
            var passwordOk = validatePassword();
            var confirmOk = validateConfirmPassword();
            if (!passwordOk || !confirmOk) {
                if (!passwordOk) addErrorClass('password');
                if (!confirmOk) addErrorClass('confirmpassword');
                e.preventDefault();
                return;
            }
            $('#submitme').attr('disabled', true).val('Installing, please wait...');
        });

        $('input').on('focus', function(){
            $(this).css('border-color','#A9A9A9');
            $(this).css('border-width','1px');
        });

        $('input').on('blur', function(){
            if (requiredFields.indexOf($(this).attr('name')) !== -1 && $(this).val() == '') {
                addErrorClass($(this).attr('name'));
            }
        });

        function addErrorClass(name){
            var target = document.getElementsByName(name);
            $(target[0]).css('border-color','red');
            $(target[0]).css('border-width','1px');
        }
    </script>
@stop
