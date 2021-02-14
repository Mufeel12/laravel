<?php
// initialising
$user = \Illuminate\Support\Facades\Auth::user();
$active_tab = (isset($active_tab) ? $active_tab : '');
$first_name = (isset($first_name) ? $first_name : $user->first_name);
$settings = $user->settings;
?><!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    @if(isset($title)) @section('title', $title) @endif
    @include('include.head')

    <link rel="shortcut icon" href="{{ asset('img/favicon.png') }}">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:700,600,800,400,300' rel='stylesheet' type='text/css'>
</head>
<body class="{!! $bodyClass or '' !!}" id="body" <?php if (isset($video->id)) echo 'video-id="' . $video->id . '"';?>>
@yield('modals')
<div id="wrapper">
    @if (!isset($disable_main_head))
        <div class="topbar">
            <div class="global-nav">
                <div class="global-nav-inner">
                    <div class="container-fluid">
                        <div role="navigation" class="row">
                            <ul class="nav pull-left" id="global-actions">
                                <li id="global-nav-home" class="home" data-global-action="home">
                                    <a href="{!! url('/') !!}">
                                        <img src="{!! asset('img/logo.svg') !!}" alt="Motion CTA"/>
                                    </a>
                                </li>
                                <li class="projects" id="topdescmenu"
                                    data-global-action="dashboard">
                                    <a href="{!! url('projects') !!}">Projects</a>
                                </li>
                                <li class="analytics"
                                    id="topdescmenu" data-global-action="analytics">
                                    <a href="{!! url('analytics') !!}">Analytics</a>
                                </li>
                                <li class="slate" id="topdescmenu"
                                    data-global-action="slate">
                                    <a href="{!! url('slates') !!}">Slate</a>
                                </li>
                                <li class="slate" id="topdescmenu"
                                    data-global-action="slate">
                                    <a href="{!! url('account/profile') !!}">Account</a>
                                </li>
                                <li class="slate" id="topdescmenu"
                                    data-global-action="slate">
                                    <a href="{!! url('help') !!}">Help</a>
                                </li>
                            </ul>
                            <ul id="global-user-actions" class="pull-right">
                                {{--<!--<li class="search" id="topdescmenu"><a href="{!! route('search') !!}"
                                                                       class="img-circle"><img
                                                src="{!! asset('img/user_action_search.png') !!}" height="16"
                                                alt=""/></a></li>-->--}}
                                <li class="notifications dropdown" id="topdescmenu">
                                    <a data-toggle="dropdown" role="button" data-target="#" id="notificationsTrigger"
                                       href="#" class="img-circle" aria-haspopup="true" aria-expanded="false">
                                        <img src="{!! asset('img/user_action_notification.png') !!}" height="16"
                                             alt=""/>
                                            <div class="badge" id="badgeal">10</div>
                                    </a>
                                </li>
                                <li class="profile dropdown" id="topdescmenu">
                                    <a data-toggle="dropdown" role="button" data-target="#" href="#" class="img-circle"
                                       aria-haspopup="true" aria-expanded="false">
                                        <img class="img-circle profile-avatar"
                                             src="{!! \Bkwld\Croppa\Facade::url($user->photo_url, 60, 60) !!}"
                                             alt=""/>
                                    </a>

                                    <!-- profile menu -->
                                    <ul class="dropdown-menu profile-dropdown text-left">
                                        <li class="main-item clearfix text-center">
                                            <div class="avatar-in-dropdown-container">
                                                <img src="{!! \Bkwld\Croppa\Facade::url($user->photo_url, 60, 60) !!}"
                                                     alt=""
                                                     class="profile-avatar img-circle manual-image-upload pointer"/>
                                                <label for="webcam"><span class="avatar-uploader"></span></label>

                                            </div>
                                            <div class="user-identifire">@if(isset($user->first_name)) {!! $user->first_name !!}
                                                <br/>{!! $user->last_name !!} @else uncurrent @endif</div>
                                        </li>
                                        <li role="presentation" class="divider"></li>
                                        <!--<li class="main-item clearfix">
                                            <a href="#"><span><i
                                                            class="pages-icon"></i>Pages</span></a>
                                        </li>-->
                                        <li class="main-item clearfix account-drop-item">
                                            <a href="{!! url('account/subscribers') !!}"
                                               class=""><span>Subscribers</span></a>
                                        </li>
                                        <!--<li class="main-item clearfix">
                                            <a href="#"><span><i class="third-party-tracking-icon"></i>3rd-Party tracking</span></a>
                                        </li>-->
                                        <li role="presentation" class="divider"></li>
                                        <li class="main-item clearfix logout account-drop-item">
                                            <a href="{!! url('logout') !!}"><span>Logout</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hamb"></div>
            <!-- mobile menu -->
            <div class="mobmenu">
                <ul id="global-user-actions">
                    <li class="profile"><a href="{!! url('account/profile') !!}" class="img-circle"><img
                                    class="img-circle profile-avatar" id="avatmob"
                                    src="{!! \Bkwld\Croppa\Facade::url($user->photo_url, 60, 60) !!}"
                                    alt=""/>

                            <div id="persname">{!! $user->first_name !!}</div>
                        </a></li>
                    <li class="notifications dropdown"><a data-toggle="dropdown" role="button" data-target="#"
                                                          id="notificationsTrigger" href="#" aria-haspopup="true"
                                                          aria-expanded="false">
                            <img src="{!! asset('img/user_action_notification-white.png') !!}" height="16" alt=""
                                 class="img-circle"/>

                            <div id="notifname">Notifications</div>
                            <div class="badge" id="badgeal">10</div>
                        </a></li>
                </ul>

                <a href="/logout">
                    <div class="logout">Logout</div>
                </a>
            </div>
        </div>
    @endif
    <div id="content">
        <div class="container-fluid top100">
            <!-- content -->
            <div class="row-fluid">
                <div class="col-md-12">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
@include('include.footer')
<script>
    window.intercomSettings = {
        app_id: "zmhhjfa6"
    };
</script>
<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/zmhhjfa6';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>
</body>
</html>
