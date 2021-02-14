@extends('slates.templates.layout')

@section('slateHead')
    @parent

    <link href="{!! asset('css/templates/earlyBird.css') !!}" rel="stylesheet" type="text/css" media="all"/>
@endsection

@section('slateContent')

    <div class="content padtb" style="text-align: center">
        <img :src="data.logo" class="logo rha">
        <br/><br/><br/><br/>
        <div class="rha fs45 semi">
            @{{ data.title }}
        </div>
        <br/><br/>
        <div class="rha fs20">
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

        <div class="halfpage froml fs21">
            <span class="day">@{{ data.countdown_day_text }}</span>
            <br/><span class="dec">@{{ data.countdown_month }}</span>
            <br/><span class="chis">@{{ data.countdown_days }}</span>
            <br/>@{{ data.countdown_time }}
        </div>

        <div class="halfpage fromr mail">

            <form class="lead_capture_form"
                  v-on:submit.prevent="submitLeadCapture()">
                <input type="hidden" v-model="action" value="{!! route('leadCapture') !!}?provider_list=@{{ data.email_provider }}&slate_id=@{{ data.slate.id }}"/>

                <div style="text-align: center;height: 50px;">
                    <input name="email"
                           type="email"
                           v-model="email"
                           v-bind:class="{ 'error': emailValidation == 'dirty' }"
                           class="tex"
                           v-show="!emailLoading"
                           v-on:keyup.enter="submitLeadCapture()"
                           placeholder="@{{ data.email_label }}"/>
                    <div v-show="emailMessage">@{{ emailMessage }}</div>
                    <img v-show="emailLoading" width="24" heigh="24" style="display: block; margin: 0 auto;" src="{!! asset('img/loading.gif') !!}" alt="loading">
                </div>

                <br/>

                <div style="text-align: center;">
                    <button type="button"
                            class="bluebut pointer"
                            @click.prevent="submitLeadCapture()">
                        <span class="pointer-none">@{{ data.email_button_text }}</span>
                    </button>
                </div>

            </form>
        </div>

        <br/><br/><br/><br/><br/><br/>

        <span class="copy">@{{ data.footer_title }}</span>

        <br/><br/>
    </div>

@endsection


@section('slateFooter')
    @parent
    <script src="//code.jquery.com/jquery-latest.js"></script>
@endsection