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
 <div id="form-content">
<div ng-app="myApp">
        <h1 style="text-align: center;">Locale Information</h1>
        {!! html()->form('POST', route('postaccount'))->attributes(['id' => 'postaccount'])->open() !!}
        

        <!-- checking if the form submit fails -->
        @if($errors->first('firstname')||$errors->first('Lastname')||$errors->first('email')||$errors->first('username')||$errors->first('password')||$errors->first('confirm_password'))
            <div class="woocommerce-message woocommerce-tracker">
                <div class="fail">
                    @if($errors->first('firstname'))
                        <span id="fail">{!! $errors->first('firstname', ':message') !!}</span><br/>
                    @endif
                    @if($errors->first('Lastname'))
                        <span id="fail">{!! $errors->first('Lastname', ':message') !!}</span><br/>
                    @endif
                    @if($errors->first('email'))
                        <span id="fail">{!! $errors->first('email', ':message') !!}</span><br/>
                    @endif
                    @if($errors->first('username'))
                        <span id="fail">{!! $errors->first('username', ':message') !!}</span><br/>
                    @endif
                    @if($errors->first('password'))
                        <span id="fail">{!! $errors->first('password', ':message') !!}</span><br/>
                    @endif
                    @if($errors->first('confirm_password'))
                        <span id="fail">{!! $errors->first('confirm_password', ':message') !!}</span><br/><br/>
                    @endif
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
            <table>                
                <p>Welcome to the five-minute Faveo installation process! Just fill in the information below.</p>
                <h1 style="border-top:1px solid #dedede; border-bottom:1px solid #dedede; padding: 10px 0px 10px 0px;">Personal Information</h1>
                <p>Please provide the following information. Don’t worry, you can always change these settings later.</p>
                <div>
                    <tr>
                        <td>
                            <label for="box1">{!! Lang::get('lang.name') !!}<span style="color
                                : red;font-size:12px;">*</span></label>
                        </td>
                        <td>
                            {!! html()->text('firstname', null)->required()->attributes(['style' => 'margin-left:250px']) !!}
                        </td>
                        <td>
                            <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Nametitle}}" data-bs-content="@{{Namecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="box2">Last Name<span style="color
                                : red;font-size:12px;">*</span></label>
                        </td>
                        <td>
                            {!! html()->text('Lastname', null)->required()->attributes(['style' => 'margin-left:250px']) !!}
                        </td>
                        <td>
                            <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Lasttitle}}" data-bs-content="@{{Lastcontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="box2">Email<span style="color
                                : red;font-size:12px;">*</span></label>
                        </td>
                        <td>
                            {!! html()->email('email', null)->required()->attributes(['style' => 'margin-left:250px']) !!}
                        </td>
                        <td>
                            <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Emailtitle}}" data-bs-content="@{{Emailcontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                            </button>
                        </td>
                    </tr>

                </div>
            </table>
            <table>
                <h1>Login Information</h1>
                <div>

                    <tr>
                        <td>
                            <label for="box4">User Name <span style="color
                                    : red;font-size:12px;">*</span>
                            </label>
                        </td>
                        <td>
                            {!! html()->text('username', null)->required()->attributes(['style' => 'margin-left:195px']) !!}
                        </td>
                        <td>
                            <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{UserNametitle}}" data-bs-content="@{{UserNamecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="box4">Password <span style="color
                                    : red;font-size:12px;">*</span>
                            </label>
                        </td>
                        <td>
                            <input type="password" name="password" style="margin-left: 195px" required="true">
                        </td>
                        <td>
                            <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Passtitle}}" data-bs-content="@{{Passcontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="box5">Confirm Password<span style="color
                                    : red;font-size:12px;">*</span>
                            </label>
                        </td>
                        <td>
                            <input type="password" name="confirmpassword" style="margin-left: 195px" required="true">
                        </td>
                        <td>
                            <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Confirmtitle}}" data-bs-content="@{{Confirmcontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                            </button>
                        </td>
                    </tr>
                </div>
            </table>
            <table>
                <h1>Locale Information</h1>
                <div>
                    <tr>
                        <td>
                            {!! html()->label(Lang::get('lang.date_time'), 'date') !!}
                        </td>
                        <td>
                            <div class="side-by-side clearfix moveleft">
                                <div>
                                    <select name="datetime" data-placeholder="Choose a date format..." class="chosen-select" style="width:295px;" tabindex="2">
                                        <option value="d/m/Y H:i:s">DD/MM/YYYY H:i:s</option>
                                        <option value="m/d/Y H:i:s">MM/DD/YYYY H:i:s</option>
                                        <option value="Y/m/d H:i:s">YYYY/MM/DD H:i:s</option>
                                    </select>
                                </div>
                            </div>
                        </td>
                        <td>
                            <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Datetimetitle}}" data-bs-content="@{{Datetimecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {!! html()->label(Lang::get('lang.time_zone'), 'time_zone') !!}
                        </td>
                        <?php  

                                    $timezonesList = \App\Model\helpdesk\Utility\Timezones::orderBy('name','ASC')->get();

                                                 //
                                    foreach ($timezonesList as $timezone) {
                                    $location = $timezone->location;
                                    $start  = strpos($location, '(');
                                    $end    = strpos($location, ')', $start + 1);
                                    $length = $end - $start;
                                    $result = substr($location, $start + 1, $length - 1);
                                    $display[]=(['id'=>$timezone->name ,'name'=> '('.$result.')'.' '.$timezone->name]);
                                    }
                                     //for display 
                                    $timezones = array_column($display,'name','id');
                                  ?>
                        <td>
                            <div class="side-by-side clearfix moveleft">
                                <div>

                     {!! html()->select('timezone', [Lang::get('lang.choose')=>$timezones], null)->class('selectpicker chosen-select')->required()->attribute('data-live-search', 'true')->attribute('data-live-search-placeholder', 'Search')->attributes(['style' => 'width:295px;']) !!}
                               </div>
                            </div>                
                        </td>
                        <td>
                            <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Timezonetitle}}" data-bs-content="@{{Timezonecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {!! html()->label(Lang::get('lang.language'), 'language') !!}
                        </td>
                        <td>
                            <div class="side-by-side clearfix moveleft">
                            <?php 
                            $path = base_path('lang');
                            $values = scandir($path);
                            $values = array_slice($values, 2);
                            $show = [];
                            foreach($values as $value) {
                                $show[$value] = Config::get('languages.' . $value)[0]."&nbsp;(".Config::get('languages.' . $value)[1].")";
                            }
                            ?>
                                {!! html()->select('language', $show, 'en')->attributes(["class"=> "chosen-select", "style"=>"width:295px;", "tabindex"=>"2"]) !!}
                           </div>
                        </td>
                        <td>
                            <button type="button" data-bs-toggle="popover" data-bs-placement="right" data-arrowcolor="#eeeeee" data-bordercolor="#bbbbbb" data-title-backcolor="#cccccc" data-title-bordercolor="#bbbbbb" data-title-textcolor="#444444" data-content-backcolor="#eeeeee" data-content-textcolor="#888888" title="@{{Languagetitle}}" data-bs-content="@{{Languagecontent}}" style="padding: 0px;border: 0px; border-radius: 5px;"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i>
                            </button>
                        </td>
                    </tr>
                </div>
            </table>
            <br><br>
            <p class="setup-actions step">
                <input type="submit" id="submitme" class="button-primary button button-large button-next" value="Install">
                <a href="{{url('step4')}}" class="button button-large button-next" style="float: left">Previous</a>
            </p>
        </form>
    </div>
    </p>
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.0/angular.min.js"></script>
    <script src="{{asset("lb-faveo/js/angular2.js")}}" type="text/javascript"></script>
    </div>
    </div>
    <script type="text/javascript">
            @if($errors->has('firstname'))
                addErrorClass('firstname');
            @endif
            @if($errors->has('lastname'))
                addErrorClass('Lastname');
            @endif
            @if($errors->has('email'))
                addErrorClass('email');
            @endif
            @if($errors->has('username'))
                addErrorClass('username');
            @endif
            @if($errors->has('password'))
                addErrorClass('password');
            @endif
            @if($errors->has('confirmpassword'))
                addErrorClass('confirmpassword');
            @endif

        $('#postaccount').on('submit', function(e) {
            $('#submitme').attr('disabled', true);
            $('#submitme').val('Installing, please wait...');
            $empty_field = 0;
            $("#postaccount input").each(function() {
                if($(this).attr('name') == 'firstname' ||
                   $(this).attr('name') == 'Lastname' ||
                   $(this).attr('name') == 'email' ||
                   $(this).attr('name') == 'username' ||
                   $(this).attr('name') == 'password' ||
                   $(this).attr('name') == 'confirmpassword'){
                    if ($(this).val() == '') {
                        $(this).css('border-color','red')
                        $(this).css('border-width','1px');
                        $empty_field = 1;
                    } else {
                        $empty_field = 0;
                    }
                }
            });
            if ($empty_field !=0 ) {
                alert('Please fill all required values.');
                e.preventDefault();
                $('#submitme').attr('disabled', false);
                $('#submitme').val('Install');
            }
        });

        $('input').on('focus', function(){
            $(this).css('border-color','#A9A9A9')
            $(this).css('border-width','1px');
        })

        $('input').on('blur', function(){
            if($(this).val() == ''){
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