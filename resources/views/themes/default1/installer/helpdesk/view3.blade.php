@extends('themes.default1.installer.layout.installer')

@section('license')
done
@stop

@section('environment')
done
@stop

@section('database')
active
@stop

@section('content')
<div ng-app="myApp">
    <h1 style="text-align: center;">Database Setup</h1>
    <p class="wc-setup-content">Below you should enter your database connection details. If you’re not sure about these, contact your host.</p>

    @if(Cache::has('fails'))
    <div class="wc-setup-content">
        <div class="woocommerce-message woocommerce-tracker">
            <div class="fail">
                <span id="fail">{!! Lang::get('lang.fails') !!}! {{Cache::get('fails')}}</span><br/><br/>
            </div>
        </div>        
    </div>  
    <?php Cache::forget('fails')?>
    @endif

    @if($errors->has('default') || $errors->has('host') || $errors->has('port') || $errors->has('databasename') || $errors->has('username') || $errors->has('password'))
    <div class="wc-setup-content">
        <div class="woocommerce-message woocommerce-tracker">
            <div class="fail">
                {!! $errors->first('default', '<spam id="fail">:message</spam><br/>') !!}
                {!! $errors->first('host', '<spam id="fail">:message</spam><br/>') !!}
                {!! $errors->first('port', '<spam id="fail">:message</spam><br/>') !!}
                {!! $errors->first('databasename', '<spam id="fail">:message</spam><br/>') !!}
                {!! $errors->first('username', '<spam id="fail">:message</spam><br/>') !!}
                {!! $errors->first('password', '<spam id="fail">:message</spam><br/>') !!}
                <br/>
            </div>
        </div>
    </div>
    @endif

    {!! html()->form('POST', url('/step4post'))->attributes(['id' => 'databaseform'])->open() !!}
    <table ng-controller="MainController">
        <tr>
            <td>
                <label for="selectbox1">Database <span style="color: red;font-size:12px;">*</span></label>
            </td>
            <td>
                <div class="side-by-side clearfix moveleftthre">
                    <div>
                        <select name="default" data-placeholder="Choose a SQL format..." class="chosen-select" style="width:288px;" tabindex="2">
                            <option value="mysql">MySQL</option>
                        </select>
                    </div>
                </div>
            </td>
            <td>
                <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Databasetitle}}" data-bs-content="@{{Databasecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                </button>
            </td>
        </tr>
        <tr>
            <td>
                <label for="box1">Host<span style="color: red;font-size:12px;">*</span></label>
            </td>
            <td>
                {!! html()->text('host', null)->required() !!}
            </td>
            <td>
                <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Hosttitle}}" data-bs-content="@{{Hostcontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                </button>
            </td>
        </tr>
        <tr>
            <td>
                <label for="box2">Port</label>
            </td>
            <td>
                {!! html()->text('port', null)->attributes(['onkeydown' => 'return CheckPortForInput(event)']) !!}
            </td>
            <td>
                <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Porttitle}}" data-bs-content="@{{Portcontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                </button>
            </td>
        </tr>
        <tr>
            <td>
                <label for="box3">Database Name<span style="color: red;font-size:12px;">*</span></label>
            </td>
            <td>
                {!! html()->text('databasename', null)->required() !!}
            </td>
            <td>
                <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Databasenametitle}}" data-bs-content="@{{Databasenamecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                </button>
            </td>
        </tr>
        <tr>
            <td>
                <label for="box4">User Name<span style="color: red; font-size: 12px;">*</span></label>
            </td>
            <td>
                {!! html()->text('username', null)->required() !!}
            </td>
            <td>
                <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Usertitle}}" data-bs-content="@{{Usercontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                </button>
            </td>
        </tr>
        <tr>
            <td>
                <label for="box5">Password</label>
            </td>
            <td>
                <input type="text" name="password"> 
            </td>
            <td>
                <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Passwordtitle}}" data-bs-content="@{{Passwordcontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                </button>
            </td>
        </tr>
    </table>
    <br>
    <p ng-controller="MainController">
        <input id="dummy-data" class="input-checkbox" type="checkbox" name="dummy-data">
            <label for="dummy-data" style="color:#3AA7D9">Install dummy data</label>
            <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{DummyDataTitle}}" data-bs-content="@{{DummyDataContent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                            </button>
    </p>
    <p class="setup-actions step">
        <input type="submit" id="submitme" class="button-primary button button-large button-next" value="Continue">
        <a href="{!! route('licence') !!}" class="button button-large button-next" style="float: left">Previous</a>
    </p>
    <br>
</form>
</div>
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.0/angular.min.js"></script>
<script src="{{asset("lb-faveo/js/angular2.js")}}" type="text/javascript"></script>
<script type="text/javascript">
    function CheckPortForInput(e) {
        var code = e.which || e.keyCode;
        if (e.ctrlKey != true){
            if((code >=48 && code<= 57) || code == 8 || code == 46 || e.keyCode == 9 || e.keyCode == 13) {
                return true;
            }
        } else {
            if((code == 65 || code == 97) || (code == 88 || code == 120) || (code == 86 || code == 118)) {
                return true;
            }
        }
        return false;
    }
</script>
<script type="text/javascript">
    @if($errors->has('host'))
        addErrorClass('host');
    @endif
    @if($errors->has('host'))
        addErrorClass('host');
    @endif
    @if($errors->has('databasename'))
        addErrorClass('databasename');
    @endif
    @if($errors->has('username'))
        addErrorClass('username');
    @endif
    @if($errors->has('password'))
        addErrorClass('password');
    @endif

    $('#databaseform').on('submit', function(e){
        var empty_field = 0;
        $("#databaseform input[type=text]").each(function(){
            if($(this).attr('name') == 'host' || $(this).attr('name') == 'databasename' || $(this).attr('name') == 'username'){
                if ($(this).val() == '') {
                    $(this).css('border-color','red')
                    $(this).css('border-width','1px');
                    empty_field = 1;
                } else {
                    empty_field = 0;
                }
            }
        });
        if (empty_field != 0) {
            e.preventDefault();
            alert('Please fill all required values.');
        }
    });

    $('input[type=text]').on('blur', function(){
        if($(this).attr('name') == 'host' || $(this).attr('name') == 'databasename' || $(this).attr('name') == 'username'){
            if ($(this).val() == '') {
                addErrorClass($(this).attr('name'));
            }
        }
    })

    function addErrorClass(name){
        var target = document.getElementsByName(name);
        $(target[0]).css('border-color','red');
        $(target[0]).css('border-width','1px');
    }

    $('input').on('focus', function(){
        $(this).css('border-color','#A9A9A9')
        $(this).css('border-width','1px');
    })
</script>
</div>
@stop