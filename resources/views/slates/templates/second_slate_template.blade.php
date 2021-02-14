@extends('slates.templates.layout')

@section('slateHead')
    @parent

    <link href="{!! asset('css/templates/second.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')
    <section v-bind:style="{ 'background-color' : data.header_background_color }"
             class="bg-primary background-multiply pt200 pb240 pt-xs-120 pb-xs-120 overlay image-bg parallax">
        <div class="background-image-holder">
            <img alt="image" class="background-image" src=""/>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 text-center">
                    <h1 style="font-weight: bold">@{{ data.title }}</h1>
                    <h4 class="mb56 mb-xs-24">
                        @{{ data.subtitle }}
                    </h4>
                    <a class="btn btn-lg btn-white mb0"
                       href="@{{ data.header_button_url }}">@{{ data.header_button_text }}</a>
                </div>
            </div>
            <!--end of row-->
        </div>
        <!--end of container-->
    </section>
    <section class="portfolio-pullup">
        <div class="container">
            <div class="row row-gapless" style="height: auto; max-width: 90%; margin: 0 auto;">

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

                <div class="text-center pt28">
                    <span class='st_facebook_large' displayText='Facebook'></span>
                    <span class='st_twitter_large' displayText='Tweet'></span>
                    <span class='st_googleplus_large' displayText='Google +'></span>
                    <span class='st_whatsapp_large' displayText='WhatsApp'></span>
                    <span class='st_email_large' displayText='Email'></span>
                </div>
            </div>
            <!--end of row-->
        </div>
        <!--end of container-->
    </section>
    <section v-bind:style="{ 'background': data.footer_background }" class="bg-dark pt64 pb30">
        <div class="container">
            <div class="row clearfix">
                <div class="col-sm-12 text-center row-space-8">
                    <h2 class="mb8">@{{ data.footer_title }}</h2>
                    <p class="lead mb40 mb-xs-24">
                        @{{ data.footer_subtitle }}
                    </p>
                    <a v-bind:style="{ 'background': data.footer_button_background }" class="btn btn-filled btn-lg mb0"
                       href="@{{ data.footer_button_url }}">@{{ data.footer_button_text }}</a>
                </div>
            </div>
            <!--end of row-->
        </div>
        <!--end of container-->
    </section>
@endsection


@section('slateFooter')
    <script type="text/javascript">var switchTo5x=true;</script>
    <script type="text/javascript" src="https://w.sharethis.com/button/buttons.js"></script>
    <script type="text/javascript">stLight.options({publisher: "ur-e2aac0f6-a9bc-ecab-8fd6-bbbb5c89ca57", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>
@endsection