@extends('slates.templates.layout')

@section('slateHead')
    @parent
    <link rel="stylesheet" href="{!! asset('css/templates/blueOcean.css') !!}"/>
@endsection

@section('slateContent')
    <section class="blueOcean">
        <div class="container">
            <h1 v-if="data.title" id="blueOceanTitle" class="headline row-space-top-6 row-space-5 row-space-inner-top-0 text-center primary-text-color">
                @{{ data.title }}
            </h1>

            <div id="video" class="row-space-top-3">
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
                    <iframe width="640" height="480" allowtransparency="true" src="{!! route('watchVideo', ['id' => (isset($slate->video_id) ? $slate->video_id : $previewVideo->id)]) !!}" frameborder="0" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen scrolling="no" style="width: 100% !important;"></iframe>
                @endif
            </div>

            <div class="row-space-top-4">
                <a v-bind:style="{'background': data.button_background }" href="@{{ data.button_url }}" target="_blank" id="landingButton" class="btn btn-lg landing-button primary-text-color button_color-color">
                    @{{ data.button_text }}
                </a>

                <div class="text-center" id="share-panel">
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
    <style type="text/css">
        body, #slate-page {
            background: @{{ data.background }} !important;
        }
    </style>
@endsection

@section('slateFooter')
    @parent
    <script type="text/javascript">var switchTo5x=true;</script>
    <script type="text/javascript" src="https://w.sharethis.com/button/buttons.js"></script>
    <script type="text/javascript">stLight.options({publisher: "ur-e2aac0f6-a9bc-ecab-8fd6-bbbb5c89ca57", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>
    <script src="//code.jquery.com/jquery-latest.js"></script>
    <script src="//cdn.rawgit.com/noelboss/featherlight/1.4.1/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>
@endsection