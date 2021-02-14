{{-- Body --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseBody"
         aria-expanded="false" aria-controls="Body">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Body
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>

<div id="collapseBody" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Body">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px">
            {{-- Body background --}}
            <div class="clearfix row-space-1">
                <p class="pull-left editor-label row-space-right-2">Background</p>
                <!-- colorpicker -->
                <div class="clearfix moveABitLeft"
                     style="margin-top: -1px">
                    <div class="img-circle pointer pull-left"
                         style="width: 20px; height: 20px;">
                        <div class="relative">
                            <div style="position: absolute; left: 0px; top: 0px;">
                                <div class="colorpicker-buttons">
                                    <div class="colorpicker-button"
                                         id="player_color"
                                         v-bind:style="{ 'background': data.background }">
                                    </div>
                                </div>
                                <input type="hidden"
                                       class="colorpicker position-left"
                                       data-model="background"/>
                            </div>
                        </div>
                    </div>
                    <div class="pull-left" style="margin-left: 5px">
                        <img src="{!! asset('img/arrow_down.png') !!}"
                             width="10"
                             alt=""
                             style="margin-top: 0px"/>
                    </div>
                </div>
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









{{-- Content --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseContent"
         aria-expanded="false" aria-controls="Content">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Content
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseContent" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Content">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- Header title --}}
            <div class="clearfix row-space-3">
                <label for="headerTitle" class="editor-label">Title</label>
                <input v-model="data.title" class="form-control" placeholder="Your header title goes here"
                       id="headerTitle" type="text" name="header_title"/>
            </div>

            <div class="clearfix">
                <div class="col-sm-6" style="padding-left: 0">
                    <div class="relative">
                        {{-- Content button text --}}
                        <div class="clearfix row-space-3">
                            <label for="contentButtonText" class="editor-label">Button text</label>
                            <input v-model="data.button_text" class="form-control" style="padding-right: 50px"
                                   placeholder="You content button text goes here" id="contentButtonText" type="text"
                                   name="button_text"/>
                        </div>

                    {{-- button background --}}
                    <!-- colorpicker -->
                        <div class="clearfix moveABitLeft"
                             style="position:absolute; right: 10px; top: 38px; margin-top: -1px">
                            <div class="img-circle pointer pull-left"
                                 style="width: 20px; height: 20px;">
                                <div class="relative">
                                    <div style="position: absolute; left: 0px; top: 0px;">
                                        <div class="colorpicker-buttons">
                                            <div class="colorpicker-button"
                                                 id="player_color"
                                                 v-bind:style="{ 'background': data.button_background }">
                                            </div>
                                        </div>
                                        <input type="hidden"
                                               class="colorpicker position-left"
                                               data-model="button_background"/>
                                    </div>
                                </div>
                            </div>
                            <div class="pull-left" style="margin-left: 5px">
                                <img src="{!! asset('img/arrow_down.png') !!}"
                                     width="10"
                                     alt=""
                                     style="margin-top: 0px"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 no-padding">
                    {{-- Content button url --}}
                    <div class="clearfix">
                        <label for="contentButtonUrl" class="editor-label">Button url</label>
                        <input v-model="data.button_url" class="form-control"
                               placeholder="You content sub-title text goes here" id="contentButtonUrl" type="text"
                               name="button_url"/>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>











