@extends('slates.templates.layout')

@section('slateHead')
    @parent

    {{--<link href='https://fonts.googleapis.com/css?family=Oswald:300,400,600,700' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Calligraffitti' rel='stylesheet' type='text/css'>
    <link href="//cdn.rawgit.com/noelboss/featherlight/1.4.1/release/featherlight.min.css" type="text/css" rel="stylesheet" />--}}
    <link href="{!! asset('css/templates/third.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')
    <section class="fullscreen image-bg">
        <div class="background-image-holder">
            <div class="full-background-image" v-bind:style="{'background-image' : 'url(' + data.header_image + ')'}"></div>
        </div>
        <div class="container v-align-transform">
            <div class="row">
                <div class="col-sm-8 col-md-6 mb24">
                    <h1>@{{ data.title }}</h1>
                </div>
                <div class="col-sm-12">
                    <div class="modal-container pull-left">
                        <a href="https://localhost:8888/CTAMonkey/public/embed/{!! (isset($slate->video_id) ? $slate->video_id : '@{{ video_id }}') !!}" data-featherlight="iframe"><div class="play-button btn-modal inline"></div></a>
                    </div>
                    <!--end of modal video-->
                    <p class="lead inline-block p32 p0-xs pt8 wrap-text">@{{ data.subtitle }}</p>
                </div>
            </div>
            <!--end of row-->
        </div>
        <!--end of container-->
    </section>
    <section class="image-square left clearfix">
        <div class="col-md-6 image">
            <div class="background-image-holder">
                <div class="full-background-image" v-bind:style="{'background-image' : 'url(' + data.content_image + ')'}"></div>
            </div>
        </div>
        <div class="col-md-6 col-md-offset-1 content">
            <h3 class="uppercase">@{{ data.content_headline }}</h3>
            <p class="wrap-text">@{{ data.content_text }}</p>
            <a class="btn btn-lg bg-dark mb0" v-bind:style="{'background' : data.content_button_background}" href="@{{ data.content_button_url }}">@{{ data.content_button_text }}</a>
        </div>
    </section>
    <footer class="footer-1 bg-dark clearfix" v-bind:style="{'background' : data.footer_background}">
        <div class="container clearfix footer-container">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="text-white">@{{ data.footer_title }}</h4>
                    <span class="sub">@{{ data.footer_subtitle }}</span>
                </div>
                <div class="col-sm-6" id="social-panel">
                    <span class='st_facebook_large' displayText='Facebook'></span>
                    <span class='st_twitter_large' displayText='Tweet'></span>
                    <span class='st_googleplus_large' displayText='Google +'></span>
                    <span class='st_whatsapp_large' displayText='WhatsApp'></span>
                    <span class='st_email_large' displayText='Email'></span>
                </div>
            </div>
        </div>
    </footer>
@endsection


@section('slateFooter')
    @parent
    <script type="text/javascript">var switchTo5x=true;</script>
    <script type="text/javascript" src="https://w.sharethis.com/button/buttons.js"></script>
    <script type="text/javascript">stLight.options({publisher: "ur-e2aac0f6-a9bc-ecab-8fd6-bbbb5c89ca57", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>

    <script src="//code.jquery.com/jquery-latest.js"></script>
    <script src="//cdn.rawgit.com/noelboss/featherlight/1.4.1/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>
@endsection