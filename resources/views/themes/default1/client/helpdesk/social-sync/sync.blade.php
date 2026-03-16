<?php
$social = new \App\Model\helpdesk\Settings\SocialMedia();
$redirect = 'social-sync';
?>
<div>
    <h3>Social Media Sync</h3>

    @if($social->checkActive('twitter'))
    <a class="btn btn-block btn-social btn-twitter" href="{{ route('social.login', ['twitter',$redirect]) }}" style="background-color: #55ACEE;color: white;">
        <span class="fa-solid fa-twitter"></span> Sign in with Twitter
    </a>
    @endif
    @if($social->checkActive('facebook'))
    <a class="btn btn-block btn-social btn-facebook" href="{{ route('social.login', ['facebook',$redirect]) }}" style="background-color: #3B5998;color: white;">
        <span class="fa-solid fa-facebook"></span> Sign in with Facebook
    </a>
    @endif
    @if($social->checkActive('google'))
    <a class="btn btn-block btn-social btn-google-plus" href="{{ route('social.login', ['google',$redirect]) }}" style="background-color: #DD4B39;color: white;">
        <span class="fa-solid fa-google"></span> Sign in with Google
    </a>
    @endif
    @if($social->checkActive('linkedin'))
    <a class="btn btn-block btn-social btn-linkedin" href="{{ route('social.login', ['linkedin',$redirect]) }}" style="background-color: #007BB6;color: white;">
        <span class="fa-solid fa-linkedin"></span> Sign in with Linkedin
    </a>
    @endif
    @if($social->checkActive('bitbucket'))
    <a class="btn btn-block btn-social btn-bitbucket" href="{{ route('social.login', ['bitbucket',$redirect]) }}" style="background-color: blue;color: white;">
        <span class="fa-solid fa-bitbucket"></span> Sign in with Bitbucket
    </a>
    @endif
    @if($social->checkActive('github'))
    <a class="btn btn-block btn-social btn-github" href="{{ route('social.login', ['github',$redirect]) }}" style="background-color: black;color: white;">
        <span class="fa-solid fa-github"></span> Sign in with Github
    </a>
    @endif
</div>