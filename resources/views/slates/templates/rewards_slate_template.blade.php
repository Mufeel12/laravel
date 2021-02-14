@extends('slates.templates.layout')

@section('slateHead')
    @parent

    <link href="{!! asset('css/templates/rewards.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')

    <div style="text-align: center;">

        <div class="content">
            <img :src="data.logo" class="logo rha">
            <div class="fs50 extra rha mto">
                @{{ data.title }}
            </div>
            <br/><br/>
            <div class="fs23 rha swt">
                @{{ data.subtitle }}
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
                    <iframe width="100%" height="560" allowtransparency="true" src="{!! route('watchVideo', ['id' => (isset($slate->video_id) ? $slate->video_id : $previewVideo->id)]) !!}" frameborder="0" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen scrolling="no"></iframe>
                @endif

            </div>



            <br/><br/><br/><br/>
            <div class="bag1">
                <div class="fs50 extra whitetext rha">
                    @{{ data.content_headline }}
                </div>
                <br/><br/>
                <div class="fs19 purptext rha">
                    @{{ data.content_subheadline }}
                </div>
            </div>
            <br/><br/><br/>
            <a href="@{{ data.button_url }}" class="redbut" style="display: inline-block;">
                @{{ data.button_text }}
            </a>
            <br/><br/><br/><br/><br/>
            <span class="graytext fs16">@{{ data.footer_title }}</span>
        </div>

    </div>

@endsection


@section('slateFooter')
    @parent
    <script src="//code.jquery.com/jquery-latest.js"></script>
@endsection