@extends('slates.templates.layout')

@section('slateHead')
    @parent

    <link href="{!! asset('css/templates/theTutor.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')
    <div style="text-align: center">


        <div class="content padtb">
            <img :src="data.logo" class="logo rha">
            <br/><br/><br/>
            <div class="rha fs50 light whitetext">
                @{{ data.title }}
            </div>
            <br/><br/><br/>

            <div class="ifrall">
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
                <div class="ifrline"></div>
            </div>
            <div class="clear"></div>

            <br/><br/><br/><br/><br/>
            <div class="rha"><a href="@{{ data.button_url }}" class="redbut" style="display: inline-block;">
                    @{{ data.button_title }}
                </a></div>
            <br/><br/><br/>
            <div class="rha"><span class="lock whitetext fs15">@{{ data.privacy_text }}</span>
            </div>
        </div>


        <div class="darkline fs18 graytext">

            <div class="content padtb" style="margin-bottom: 50px">
                @{{ data.footer_title }}
            </div>

        </div>

    </div>
@endsection


@section('slateFooter')
    @parent
    <script src="//code.jquery.com/jquery-latest.js"></script>
@endsection