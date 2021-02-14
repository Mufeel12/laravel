@extends('slates.templates.layout')

@section('slateHead')
    @parent

    <link href='https://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic' rel='stylesheet'
          type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Raleway:700,400,300" rel="stylesheet" type="text/css">
    <link href="{!! asset('css/templates/travel.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')
    <section class="features features-1 bg-dark" v-bind:style="{'background': data.background}">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 text-center">
                    <div class="embed-video-container">
                        @if(isset($standAlone) && isset($video_thumbnail))
                            <style type="text/css">
                                .full-bg-image {
                                    background-image: url({!! $video_thumbnail or '' !!});
                                    background-position: no-repeat center center fixed;
                                    -webkit-background-size: cover;
                                    -moz-background-size: cover;
                                    -o-background-size: cover;
                                    background-size: cover;
                                    width: 100%;
                                    height: 560px;
                                }
                            </style>
                            <div class="full-bg-image">&nbsp;</div>
                        @else
                            <iframe width="640" height="600" allowtransparency="true"
                                    src="{!! route('watchVideo', ['id' => (isset($slate->video_id) ? $slate->video_id : $previewVideo->id)]) !!}"
                                    frameborder="0" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen scrolling="no"
                                    style="width: 100% !important;"></iframe>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row content">
                <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 text-center">
                    <h3>@{{ data.title }}</h3>
                    <p class="lead wrap-text">@{{ data.subtitle }}</p>
                    <div>
                        <a class="btn btn-filled" v-bind:style="{ 'background': data.button_background, 'border-color': data.button_background }" href="@{{ data.button_url }}">@{{ data.button_text }}</a>
                    </div>
                    <div id="social-share">
                        <span class='st_facebook_large' displayText='Facebook'></span>
                        <span class='st_twitter_large' displayText='Tweet'></span>
                        <span class='st_googleplus_large' displayText='Google +'></span>
                        <span class='st_whatsapp_large' displayText='WhatsApp'></span>
                        <span class='st_email_large' displayText='Email'></span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('slateFooter')
    @parent
    <script type="text/javascript">var switchTo5x=true;</script>
    <script type="text/javascript" src="https://w.sharethis.com/button/buttons.js"></script>
    <script type="text/javascript">stLight.options({publisher: "ur-e2aac0f6-a9bc-ecab-8fd6-bbbb5c89ca57", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>
    <style type="text/css">
        body {
            background: @{{ data.background }};
        }
    </style>
@endsection