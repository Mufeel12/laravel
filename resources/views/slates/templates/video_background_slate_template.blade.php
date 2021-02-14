@extends('slates.templates.layout')

@section('slateHead')
    @parent

    <link href='https://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic' rel='stylesheet'
          type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Raleway:700,400,300" rel="stylesheet" type="text/css">
    <link href="{!! asset('css/templates/travel.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')
    <section class="image-bg fullscreen overlay bg-dark vid-bg">
        <div class="player mb_YTPlayer isMuted" data-video-id="dmgomCutGqc" data-start-at="22"
             data-property="{videoURL:'https://youtu.be/dmgomCutGqc',containment:'self',autoPlay:true, mute:true, startAt:22, opacity:1, showControls:false}"
             id="video_1467367982736" style="background-image: none;">
            <div class="mbYTP_wrapper" id="wrapper_mbYTP_video_1467367982736"
                 style="position: absolute; z-index: 0; min-width: 100%; min-height: 100%; left: 0px; top: 0px; overflow: hidden; opacity: 1; transition-property: opacity; transition-duration: 2000ms;">
                <iframe width="640" height="600" allowtransparency="true"
                        src="{!! route('watchVideo', ['id' => (isset($slate->video_id) ? $slate->video_id : $previewVideo->id)]) !!}?autoplay=1&no_ads=true"
                        frameborder="0" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen scrolling="no"
                        style="width: 100% !important; height: 100%; position: absolute; z-index: 0;top: 0px; left: 0px; overflow: hidden; opacity: 1;"></iframe>
                <div class="YTPOverlay"ev
                     style="position: absolute; top: 0px; left: 0px; width: 100%; height: 100%;"></div>
            </div>
        </div>
        <div class="background-image-holder fadeIn" style="background: url(&quot;img/home15.jpg&quot;);">
            <img alt="image" class="background-image" src="img/home15.jpg" style="display: none;">
        </div>
        <div class="masonry-loader fadeOut">
            <div class="spinner">
            </div>
        </div>
        <div class="container v-align-transform">
            <div class="row">
                <div class="col-sm-12 text-center">
                    <h1 class="uppercase">Youtube Video BG</h1>
                    <p class="lead">Set the Youtube video by replacing the 'data-video-id' attribute with your source.
                        <br> Change the starting time by modifying the 'data-start-at' attribute.</p>
                </div>
            </div>
            <!--end of row-->
        </div>
        <!--end of container-->
    </section>
    <section class="features features-1 bg-dark" v-bind:style="{'background': data.background}">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 text-center">
                    <div class="embed-video-container">
                        <iframe width="640" height="600" allowtransparency="true"
                                src="{!! route('watchVideo', ['id' => (isset($slate->video_id) ? $slate->video_id : $previewVideo->id)]) !!}"
                                frameborder="0" allowfullscreen mozallowfullscreen webkitallowfullscreen
                                oallowfullscreen msallowfullscreen scrolling="no"
                                style="width: 100% !important;"></iframe>
                    </div>
                </div>
            </div>

            <div class="row content">
                <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 text-center">
                    <h3>@{{ data.title }}</h3>
                    <p class="lead wrap-text">@{{ data.subtitle }}</p>
                    <a class="btn btn-filled"
                       v-bind:style="{ 'background': data.button_background, 'border-color': data.button_background }"
                       href="@{{ data.button_url }}">@{{ data.button_text }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('slateFooter')
    <style type="text/css">
        body {
            background: @{{ data.background }};
        }
    </style>
@endsection