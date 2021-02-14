@extends('slates.templates.layout')

@section('slateHead')
    @parent

    <link href="{!! asset('css/templates/easyDownload.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')

    <div style="text-align: center">
        <div class="content padtb">
            <div class="logoline">
                <img :src="data.logo" class="logo rha">
            </div>
            <br/><br/>
            <div class="fs40 bold rha">
                @{{ data.title }}
            </div>
            <br/><br/>

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
                    <iframe width="100%" height="560" allowtransparency="true"
                            src="{!! route('watchVideo', ['id' => (isset($slate->video_id) ? $slate->video_id : $previewVideo->id)]) !!}"
                            frameborder="0" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen
                            msallowfullscreen scrolling="no"></iframe>
                @endif

            </div>

            <br><br>

            <div class="fs19 semi free rha">@{{ data.countdown_title }}</div>
            <div class="my-clock-place my-clock-place1 rha">
                <div class="countitround date-countdown relative">
                </div>
                <!--<div class="countitround" id="countdown-instance-id-05">
                    <div class="countitround_days"></div>
                    <div class="countitround_hours"></div>
                    <div class="countitround_minutes"></div>
                    <div class="countitround_seconds"></div>
                </div>-->
            </div>
        </div>

        <div class="blueline">
            <div class="content padtb">
                <br/><br/><br/><br/>
                <div class="rha">
                    <a class="but" href="@{{ data.button_url }}">
                        @{{ data.button_text }}
                    </a>
                </div>
                <br/><br/>
                <div class="fs15 graytext rha">
                    @{{ data.terms_text }}
                    <br/><br/>
                </div>
            </div>
        </div>

        <div style="height: 2px;"></div>

        <div class="blueline" id="slateFooter">
            <div class="content padtb fs16">
                <br/><br/>
                <div class="copy">@{{ data.footer_title }}</div>
                <br/><br/><br/><br/>
            </div>
        </div>
    </div>

@endsection


@section('slateFooter')
    @parent
    <script src="//code.jquery.com/jquery-latest.js"></script>
@endsection