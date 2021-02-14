@extends('slates.templates.layout')

@section('slateHead')
    @parent

    <link href="{!! asset('css/templates/theTutor2.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')
    <div class="firstbg">

        <div class="content padtb">
            <img :src="data.logo" class="logo rha">
            <br/><br/><br/>
            <div class="fs45 light darktext rha">
                @{{ data.title }}
            </div>
            <br/><br/>
            <div class="macbook rha">
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
                        <iframe width="560" height="325" allowtransparency="true" src="{!! route('watchVideo', ['id' => (isset($slate->video_id) ? $slate->video_id : $previewVideo->id)]) !!}" frameborder="0" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen scrolling="no"></iframe>
                    @endif

                </div>
            </div>
            <br/><br/>
            <div class="rha graytext fs20">
                @{{ data.subtitle }}
            </div>
            <br/><br/>
            <div class="rha">
                <a href="@{{ data.button_url }}" style="display: inline-block" class="bluebut">
                    @{{ data.button_title }}
                </a>
            </div>
        </div>

    </div>

    <br/><br/>
    <div class="grayline"></div>
    <br/><br/>

    <div class="bg2">

        <div class="content padtb">
            <div class="bluetext fs20 bold rha">@{{ data.webinar_date }}</div>
            <br/><br/>
            <div class="fs41 light rha">@{{ data.webinar_title }}</div>
            <br/><br/>
            <div class="halfpage">

                <div class="halfpage1 right">
                    <span class="extra fs23 upper">@{{ data.tutor_1_name }}</span>
                    <br/><span class="italic fs18">@{{ data.tutor_1_title }}</span>
                </div>
                <div class="halfpage1">
                    <img src="@{{ data.tutor_1_image }}" width="90%">
                </div>

            </div>
            <div class="halfpage">

                <div class="halfpage1">
                    <img src="@{{ data.tutor_2_image }}" width="90%">
                </div>
                <div class="halfpage1 left">
                    <span class="extra fs23 upper">@{{ data.tutor_2_name }}</span>
                    <br/><span class="italic fs18">@{{ data.tutor_2_title }}</span>
                </div>

            </div>
            <br/><br/>
            <div class="rha graytext fs20">
                @{{ data.tutor_text }}
            </div>
            <br/><br/>
            <div class="rha"><a href="@{{ data.second_button_url }}" class="bluebut1" style="display: inline-block;">
                    @{{ data.second_button_title }}
                </a></div>
        </div>

    </div>

    <br/><br/>
    <div class="grayline"></div>
    <br/><br/>


    <div class="content padtb">
        <div class="rha graytext fs18" style="margin-bottom: 100px">@{{ data.footer_title }}</div>
    </div>

@endsection


@section('slateFooter')
    @parent
    <script src="//code.jquery.com/jquery-latest.js"></script>
@endsection