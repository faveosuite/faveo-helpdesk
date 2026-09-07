<?php
$social = new \App\Model\helpdesk\Settings\SocialMedia();
?>
@if($social->checkActive('twitter') || $social->checkActive('facebook') || $social->checkActive('google') || $social->checkActive('linkedin') || $social->checkActive('bitbucket') || $social->checkActive('github'))
<br>
@endif

@if($social->checkActive('twitter'))
<a class="btn btn-block btn-social btn-twitter" href="{{ route('social.login', ['twitter']) }}" style="background-color: #55ACEE;color: white;">
    <span class="fa-brands fa-twitter fa-fw"></span> Sign in with Twitter
</a>
@endif
@if($social->checkActive('facebook'))
<a class="btn btn-block btn-social btn-facebook" href="{{ route('social.login', ['facebook']) }}" style="background-color: #3B5998;color: white; --bs-btn-font-size:none">
    <span class="fa-brands fa-facebook-f fa-fw"></span> Sign in with Facebook
</a>
@endif
@if($social->checkActive('google'))
<a class="btn btn-block btn-social btn-google-plus" href="{{ route('social.login', ['google']) }}" style="background-color: #DD4B39;color: white;">
    <span class="fa-brands fa-google-plus-g fa-fw"></span> Sign in with Google
</a>
@endif
@if($social->checkActive('linkedin'))
<a class="btn btn-block btn-social btn-linkedin" href="{{ route('social.login', ['linkedin']) }}" style="background-color: #007BB6;color: white;">
    <span class="fa-brands fa-linkedin-in fa-fw"></span> Sign in with Linkedin
</a>
@endif
@if($social->checkActive('bitbucket'))
<a class="btn btn-block btn-social btn-bitbucket" href="{{ route('social.login', ['bitbucket']) }}" style="background-color: blue;color: white;">
    <span class="fa-brands fa-bitbucket"></span> Sign in with Bitbucket
</a>
@endif
@if($social->checkActive('github'))
<a class="btn btn-block btn-social btn-github" href="{{ route('social.login', ['github']) }}" style="background-color: black;color: white;">
    <span class="fa-brands fa-github-alt fa-fw"></span> Sign in with Github
</a>
@endif