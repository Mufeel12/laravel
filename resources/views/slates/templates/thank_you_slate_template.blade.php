@extends('slates.templates.layout')

@section('slateHead')
    @parent

    <link href="{!! asset('css/templates/thankYou.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')
    <div id="fb-root"></div>
    <script>(function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.8&appId=188857494496677";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>

    <div style="text-align: center">

        <div class="bg1">

            <div class="content padt">
                <img :src="data.logo" class="logo rha">
                <br/><br/><br/><br/>
                <div class="halfpage froml left zin2">
                    <h1>@{{ data.title }}</h1>
                    <br/>
                    <span class="fs18">@{{ data.subtitle }}</span>
                    <br/><br/><br/><br/>
                    <a style="display: inline-block" href="@{{ data.button_url }}" class="greenbut">
                        @{{ data.button_title }}
                    </a>
                    <br/><br/><br/><br/>
                </div>
                <div class="halfpage fromr zin1">
                    <img :src="data.image" class="pic2">
                </div>
            </div>

        </div>


        <div class="content padtb">
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
            <br/><br/>
            <div class="fb-comments" data-href="@{{ data.comments_url }}"
                 data-width="100%" data-numposts="5"></div>
        </div>


        <div class="footer fs18" style="margin-bottom: 150px;">

            <div class="content padtb">
                @{{ data.footer_title }}
            </div>

        </div>

    </div>
@endsection


@section('slateFooter')
    @parent
    <script src="//code.jquery.com/jquery-latest.js"></script>
    <script type="text/javascript">
        /*jQuery(document).ready(function () {
            jQuery('.rha').addClass("hidden").viewportChecker({
                classToAdd: 'gently', // Class to add to the elements when they are visible
                offset: 0
            });
            jQuery('.fromr').addClass("hidden").viewportChecker({
                classToAdd: 'fromright', // Class to add to the elements when they are visible
                offset: 0
            });
            jQuery('.froml').addClass("hidden").viewportChecker({
                classToAdd: 'fromleft', // Class to add to the elements when they are visible
                offset: 0
            });
        });*/
    </script>
@endsection