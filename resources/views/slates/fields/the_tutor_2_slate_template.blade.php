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

            {{-- sub title --}}
            <div class="clearfix row-space-3">
                <label for="subtitle" class="editor-label">Sub-Title</label>
                <input v-model="data.subtitle" class="form-control" placeholder="Your sub-title goes here"
                       id="subtitle" type="text" name="subtitle"/>
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

            {{-- button_title --}}
            <div class="clearfix row-space-3">
                <label for="button_title" class="editor-label">Button title</label>
                <input v-model="data.button_title" class="form-control" placeholder="Download my bonuses"
                       id="button_title" type="text" name="button_title"/>
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






{{-- Second Button --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseSecondButton"
         aria-expanded="false" aria-controls="Second Button">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Second Button
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseSecondButton" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Second Button">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- second_button_title --}}
            <div class="clearfix row-space-3">
                <label for="second_button_title" class="editor-label">Button title</label>
                <input v-model="data.second_button_title" class="form-control" placeholder="Download my bonuses"
                       id="second_button_title" type="text" name="second_button_title"/>
            </div>

            {{-- second_button_url --}}
            <div class="clearfix row-space-3">
                <label for="second_button_url" class="editor-label">Button url</label>
                <input v-model="data.second_button_url" class="form-control" placeholder=""
                       id="second_button_url" type="text" name="second_button_url"/>
            </div>

        </div>
    </div>
</div>







{{-- Webinar --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseWebinar"
         aria-expanded="false" aria-controls="Webinar">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Webinar
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseWebinar" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Webinar">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- webinar_date --}}
            <div class="clearfix row-space-3">
                <label for="webinar_date" class="editor-label">Webinar date</label>
                <input v-model="data.webinar_date" class="form-control" placeholder="Download my bonuses"
                       id="webinar_date" type="text" name="webinar_date"/>
            </div>

            {{-- webinar_title --}}
            <div class="clearfix row-space-3">
                <label for="webinar_title" class="editor-label">Webinar title</label>
                <input v-model="data.webinar_title" class="form-control" placeholder=""
                       id="webinar_title" type="text" name="webinar_title"/>
            </div>

        </div>
    </div>
</div>







{{-- Tutorial text --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseTutorialtext"
         aria-expanded="false" aria-controls="Tutorial text">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Tutorial text
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseTutorialtext" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Tutorial text">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- webinar_date --}}
            <div class="clearfix row-space-3">
                <label for="tutor_text" class="editor-label">Tutorial text</label>
                <input v-model="data.tutor_text" class="form-control" placeholder="Download my bonuses"
                       id="tutor_text" type="text" name="tutor_text"/>
            </div>

        </div>
    </div>
</div>






{{-- Tutorial 1 --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseTutorial"
         aria-expanded="false" aria-controls="Tutorial 1">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Tutorial 1
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseTutorial" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Tutorial 1">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- tutor_1_image --}}
            <div class="clearfix row-space-1">
                <p class="pull-left editor-label row-space-right-2">Image</p>
                {{-- Header background image --}}
                <div class="col-sm-12 pointer row-space-top-3 row-space-3 full-bg-image img-rounded text-center image-library-button"
                     @click.prevent="this.$dispatch('open-image-library', this, $event)"
                     style="height: 150px;"
                     data-image-model="tutor_1_image"
                     v-bind:style="{'background-image': 'url(' + crop(data.tutor_1_image, 200, 100) + ')'}">
                    <span style="color: #FFFFFF; margin-top: 70px" class="inline-block">Image</span>
                </div>
            </div>

            {{-- tutor_1_name --}}
            <div class="clearfix row-space-3">
                <label for="tutor_1_name" class="editor-label">Name</label>
                <input v-model="data.tutor_1_name" class="form-control" placeholder="Download my bonuses"
                       id="tutor_1_name" type="text" name="tutor_1_name"/>
            </div>

            {{-- tutor_1_title --}}
            <div class="clearfix row-space-3">
                <label for="tutor_1_title" class="editor-label">Title</label>
                <input v-model="data.tutor_1_title" class="form-control" placeholder=""
                       id="tutor_1_title" type="text" name="tutor_1_title"/>
            </div>

        </div>
    </div>
</div>






{{-- Tutorial 2 --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseTutorial2"
         aria-expanded="false" aria-controls="Tutorial 2">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Tutorial 2
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseTutorial2" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Tutorial 2">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- tutor_2_image --}}
            <div class="clearfix row-space-1">
                <p class="pull-left editor-label row-space-right-2">Image</p>
                {{-- Header background image --}}
                <div class="col-sm-12 pointer row-space-top-3 row-space-3 full-bg-image img-rounded text-center image-library-button"
                     @click.prevent="this.$dispatch('open-image-library', this, $event)"
                     style="height: 150px;"
                     data-image-model="tutor_2_image"
                     v-bind:style="{'background-image': 'url(' + crop(data.tutor_2_image, 200, 100) + ')'}">
                    <span style="color: #FFFFFF; margin-top: 70px" class="inline-block">Image</span>
                </div>
            </div>

            {{-- tutor_2_name --}}
            <div class="clearfix row-space-3">
                <label for="tutor_2_name" class="editor-label">Name</label>
                <input v-model="data.tutor_2_name" class="form-control" placeholder="Download my bonuses"
                       id="tutor_2_name" type="text" name="tutor_2_name"/>
            </div>

            {{-- tutor_2_title --}}
            <div class="clearfix row-space-3">
                <label for="tutor_2_title" class="editor-label">Title</label>
                <input v-model="data.tutor_2_title" class="form-control" placeholder=""
                       id="tutor_2_title" type="text" name="tutor_2_title"/>
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
                <input v-model="data.footer_title" class="form-control" placeholder="Enter your email"
                       id="footer_title" type="text" name="footer_title"/>
            </div>

        </div>
    </div>
</div>
