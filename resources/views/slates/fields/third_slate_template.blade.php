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

            {{-- Header background image --}}
            <div class="col-sm-12 pointer row-space-top-3 row-space-3 full-bg-image img-rounded text-center image-library-button"
                 @click.prevent="this.$dispatch('open-image-library', this, $event)"
                 style="height: 150px;"
                 data-image-model="header_image"
                 v-bind:style="{'background-image': 'url(' + crop(data.header_image, 200, 100) + ')'}">
                <span style="color: #FFFFFF; margin-top: 70px" class="inline-block">Header background image</span>
            </div>

            {{-- Header title --}}
            <div class="clearfix row-space-3">
                <label for="headerTitle" class="editor-label">Title</label>
                <input v-model="data.title" class="form-control" placeholder="Your header title goes here"
                       id="headerTitle" type="text" name="header_title"/>
            </div>

            {{-- Sub headline --}}
            <div class="clearfix row-space-3">
                <label for="subHeadline" class="editor-label">Video description</label>
                <textarea v-model="data.subtitle" class="form-control" rows="3" placeholder="Your video description goes here"></textarea>
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

            {{-- Header background image --}}
            <div class="col-sm-12 pointer row-space-top-3 row-space-3 full-bg-image img-rounded text-center image-library-button"
                 @click.prevent="this.$dispatch('open-image-library', this, $event)"
                 style="height: 150px;"
                 data-image-model="content_image"
                 v-bind:style="{'background-image': 'url(' + crop(data.content_image, 200, 100) + ')'}">
                <span style="color: #FFFFFF; margin-top: 70px" class="inline-block">Content image</span>
            </div>

            {{-- Content title --}}
            <div class="clearfix row-space-3">
                <label for="contentHeadline" class="editor-label">Headline</label>
                <input v-model="data.content_headline" class="form-control"
                       placeholder="You content headline goes here" id="contentHeadline" type="text"
                       name="content_headline"/>
            </div>

            {{-- Content text --}}
            <div class="clearfix row-space-3">
                <label for="contentText" class="editor-label">Text</label>
                <textarea v-model="data.content_text" class="form-control" id="contentText" rows="5" placeholder="Your content text goes here"></textarea>
            </div>

            <div class="clearfix">
                <div class="col-sm-6" style="padding-left: 0">
                    <div class="relative">
                        {{-- Content button text --}}
                        <div class="clearfix row-space-3">
                            <label for="contentButtonText" class="editor-label">Button text</label>
                            <input v-model="data.content_button_text" class="form-control" style="padding-right: 50px"
                                   placeholder="You content button text goes here" id="contentButtonText" type="text"
                                   name="content_button_text"/>
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
                                                 v-bind:style="{ 'background': data.content_button_background }">
                                            </div>
                                        </div>
                                        <input type="hidden"
                                               class="colorpicker position-left"
                                               data-model="content_button_background"/>
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
                        <input v-model="data.content_button_url" class="form-control"
                               placeholder="You content sub-title text goes here" id="contentButtonUrl" type="text"
                               name="content_button_url"/>
                    </div>
                </div>
            </div>

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
<div id="collapseFooter" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Footer">
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

        </div>
    </div>
</div>
