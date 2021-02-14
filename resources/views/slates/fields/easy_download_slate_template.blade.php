{{-- Body --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseLogo"
         aria-expanded="false" aria-controls="Logo">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Logo
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>

<div id="collapseLogo" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Body">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px">
            {{-- Body background --}}
            <div class="clearfix row-space-1">
                <p class="pull-left editor-label row-space-right-2">Logo</p>
                {{-- Header background image --}}
                <div class="col-sm-12 pointer row-space-top-3 row-space-3 full-bg-image img-rounded text-center image-library-button"
                     @click.prevent="this.$dispatch('open-image-library', this, $event)"
                     style="height: 150px;"
                     data-image-model="logo"
                     v-bind:style="{'background-image': 'url(' + crop(data.logo, 200, 100) + ')'}">
                    <span style="color: #FFFFFF; margin-top: 70px" class="inline-block">Logo image</span>
                </div>
            </div>
        </div>
    </div>
</div>








{{-- Titles --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseTitles"
         aria-expanded="false" aria-controls="Titles">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Titles
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseTitles" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Titles">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- Header title --}}
            <div class="clearfix row-space-3">
                <label for="headerTitle" class="editor-label">Title</label>
                <input v-model="data.title" class="form-control" placeholder="Your title goes here"
                       id="headerTitle" type="text" name="header_title"/>
            </div>

        </div>
    </div>
</div>










{{-- Video --}}
<div class="grey-dashed ">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseVideo"
         aria-expanded="false" aria-controls="Video">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Video
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>

<div id="collapseVideo" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Video">
    <div class="row-space-top-3 row-space-3 clearfix">
        <div class="row-space-top-1 row-space-1" style="padding: 0 10px;">

            @include('slates.video_selector')

        </div>
    </div>
</div>








{{-- Countdown --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseCountdown"
         aria-expanded="false" aria-controls="Countdown">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Countdown
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseCountdown" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Countdown">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">


            {{-- sub title --}}
            <div class="clearfix row-space-3">
                <label for="subtitle" class="editor-label">Countdown-Title</label>
                <input v-model="data.countdown_title" class="form-control" placeholder="FREE TRAINING ENDING IN..."
                       id="subtitle" type="text" name="countdown_title"/>
            </div>

            {{-- countdown_time --}}
            <!-- timer -->
            <div class="row-space-top-4 row-space-2 clearfix">
                <div class="date-wrapper">
                    <div class="col-sm-8 no-padding">
                        <div class="datepicker-input relative">
                            <input type="text"
                                   v-model="data.countdown_date"
                                   class="form-control">
                            <i class="calendar-icon"></i>
                        </div>
                    </div>
                    <div class="col-sm-4 relative"
                         style="padding-right: 0;">
                        <div class="timepicker-input relative">
                            <input class="form-control timepicker"
                                   v-model="data.countdown_time"/>
                            <i class="timepicker-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>












{{-- Button --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseButton"
         aria-expanded="false" aria-controls="Button">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Button
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseButton" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Button">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- button_text --}}
            <div class="clearfix row-space-3">
                <label for="button_text" class="editor-label">Button text</label>
                <input v-model="data.button_text" class="form-control" placeholder="Instantly Download This Mindmap and System"
                       id="button_text" type="text" name="button_text"/>
            </div>

            {{-- button_url --}}
            <div class="clearfix row-space-3">
                <label for="button_url" class="editor-label">Button url</label>
                <input v-model="data.button_url" class="form-control" placeholder=""
                       id="button_url" type="text" name="button_url"/>
            </div>

        </div>
    </div>
</div>














{{-- footer --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseFooter"
         aria-expanded="false" aria-controls="Footer">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Footer
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseFooter" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Footer">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- footer --}}
            <div class="clearfix row-space-3">
                <label for="footer_title" class="editor-label">Footer</label>
                <input v-model="data.footer_title" class="form-control" placeholder="(C) Copyright 2017 - Motion CTA"
                       id="footer_title" type="text" name="footer_title"/>
            </div>

        </div>
    </div>
</div>
