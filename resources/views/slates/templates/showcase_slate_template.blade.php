@extends('slates.templates.layout')

@section('slateHead')
    @parent

    <link href="{!! asset('css/templates/showcase.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')
    <div style="text-align: center">

        <div class="topbg">
            <div class="content">
                <div class="fs51 extra whitetext rha">
                    @{{ data.title }}
                </div>
                <br/><br/>
                <div class="tab">
                    <div class="froml leftpart">
                        <div id="video">

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
                    </div>
                    <div class="fromr rightpart fs21 whitetext left">
                        <ul>
                            <li v-if="data.bullet_1"><span class="yellowtext">@{{ data.bullet_1 }}</span></li>
                            <li v-if="data.bullet_2"><span class="yellowtext">@{{ data.bullet_2 }}</span></li>
                            <li v-if="data.bullet_3"><span class="yellowtext">@{{ data.bullet_3 }}</span></li>
                            <li v-if="data.bullet_4"><span class="yellowtext">@{{ data.bullet_4 }}</span></li>
                            <li v-if="data.bullet_5"><span class="yellowtext">@{{ data.bullet_5 }}</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <br/><br/>

        <div class="content fs16 graytext">
            <div class="colls3 left rha">
                <div style="text-align: center">
                    <img src="@{{ data.box_1_image }}">
                </div>
                <br/>
                <div class="blacktext semi fs22">
                    @{{ data.box_1_title }}
                </div>
                <br/>
                @{{ data.box_1_text }}
            </div>
            <div class="colls3 left rha">
                <div style="text-align: center">
                    <img src="@{{ data.box_2_image }}">
                </div>
                <br/>
                <div class="blacktext semi fs22">
                    @{{ data.box_2_title }}
                </div>
                <br/>
                @{{ data.box_2_text }}
            </div>
            <div class="colls3 left rha">
                <div style="text-align: center">
                    <img src="@{{ data.box_3_image }}">
                </div>
                <br/>
                <div class="blacktext semi fs22">
                    @{{ data.box_3_title }}
                </div>
                <br/>
                @{{ data.box_3_text }}
            </div>
            <br/><br/><br/><br/>
            <a class="orangebut" style="display: inline-block" href="@{{ data.button_url }}">
                @{{ data.button_text }}
            </a>
            <br/><br/><br/><br/>
            <div id="slateFooter" style="margin-bottom: 150px;">
                @{{ data.footer_title }}
            </div>
        </div>

    </div>
@endsection


@section('slateFooter')
    @parent
    <script src="//code.jquery.com/jquery-latest.js"></script>
@endsection