{{-- Header --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseHeader"
         aria-expanded="false" aria-controls="Header">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Header
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>

<div id="collapseHeader" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Header">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px">
            {{-- Header background --}}
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
                                         v-bind:style="{ 'background': data.header_background_color }">
                                    </div>
                                </div>
                                <input type="hidden"
                                       class="colorpicker position-left"
                                       data-model="header_background_color"/>
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

            {{--
            <div class="col-sm-12 row-space-top-3 row-space-3">
                <a href="#" style="background: #ffffff; display: block; padding: 0 10px"
                   class="inline-block no-underline pull-left open-image-library-button"
                   @click.prevent="this.$dispatch('open-image-library', this)">
                    <span class="upload-custom-label">Upload custom</span>
                </a>
            </div>
            --}}

            {{-- Header title --}}
            <div class="clearfix row-space-3">
                <label for="headerTitle" class="editor-label">Title</label>
                <input v-model="data.title" class="form-control" placeholder="Your header title goes here"
                       id="headerTitle" type="text" name="header_title"/>
            </div>

            {{-- Sub headline --}}
            <div class="clearfix row-space-3">
                <label for="subHeadline" class="editor-label">Sub headline</label>
                <input v-model="data.subtitle" class="form-control"
                       placeholder="You sub-headline goes here" id="subHeadline" type="text" name="subtitle"/>
            </div>

            <div class="clearfix">
                <div class="col-sm-6" style="padding-left: 0">
                    {{-- Button text --}}
                    <div class="clearfix row-space-3">
                        <label for="headerButton" class="editor-label">Button text</label>
                        <input v-model="data.header_button_text" class="form-control"
                               placeholder="You header button text goes here" id="headerButton" type="text"
                               name="header_button_text"/>
                    </div>
                </div>
                <div class="col-sm-6 no-padding">
                    {{-- Button url --}}
                    <div class="clearfix row-space-3">
                        <label for="headerButtonUrl" class="editor-label">Button url</label>
                        <input v-model="data.header_button_url" class="form-control"
                               placeholder="You header button url goes here" id="headerButtonUrl" type="text"
                               name="header_button_url"/>
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













{{-- Footer --}}
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
<div id="collapseFooter" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Header">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">
            {{-- Footer background --}}
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
                                         v-bind:style="{ 'background': data.footer_background }">
                                    </div>
                                </div>
                                <input type="hidden"
                                       class="colorpicker position-left"
                                       data-model="footer_background"/>
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

            {{-- Footer title --}}
            <div class="clearfix row-space-3">
                <label for="footerTitle" class="editor-label">Title</label>
                <input v-model="data.footer_title" class="form-control"
                       placeholder="You footer title text goes here" id="footerTitle" type="text"
                       name="footer_title"/>
            </div>

            {{-- Footer subtitle --}}
            <div class="clearfix row-space-3">
                <label for="footerSubTitle" class="editor-label">Sub title</label>
                <input v-model="data.footer_subtitle" class="form-control"
                       placeholder="You footer sub-title text goes here" id="footerSubTitle" type="text"
                       name="footer_subtitle"/>
            </div>

            <div class="clearfix">
                <div class="col-sm-6" style="padding-left: 0">
                    <div class="relative">
                        {{-- Footer button text --}}
                        <div class="clearfix row-space-3">
                            <label for="footerButtonText" class="editor-label">Button text</label>
                            <input v-model="data.footer_button_text" class="form-control" style="padding-right: 50px"
                                   placeholder="You footer button text goes here" id="footerButtonText" type="text"
                                   name="footer_button_text"/>
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
                                                 v-bind:style="{ 'background': data.footer_button_background }">
                                            </div>
                                        </div>
                                        <input type="hidden"
                                               class="colorpicker position-left"
                                               data-model="footer_button_background"/>
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
                    {{-- Footer button url --}}
                    <div class="clearfix">
                        <label for="footerButtonUrl" class="editor-label">Button url</label>
                        <input v-model="data.footer_button_url" class="form-control"
                               placeholder="You footer sub-title text goes here" id="footerButtonUrl" type="text"
                               name="footer_button_url"/>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
