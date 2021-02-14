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






{{-- Bullets --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseBullets"
         aria-expanded="false" aria-controls="Bullets">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Bullets
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseBullets" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Bullets">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- bullet_1 --}}
            <div class="clearfix row-space-3">
                <label for="bullet_1" class="editor-label">Bullet 1</label>
                <input v-model="data.bullet_1" class="form-control" placeholder=""
                       id="bullet_1" type="text" name="bullet_1"/>
            </div>

            {{-- bullet_2 --}}
            <div class="clearfix row-space-3">
                <label for="bullet_2" class="editor-label">Bullet 2</label>
                <input v-model="data.bullet_2" class="form-control" placeholder=""
                       id="bullet_2" type="text" name="bullet_2"/>
            </div>

            {{-- bullet_3 --}}
            <div class="clearfix row-space-3">
                <label for="bullet_3" class="editor-label">Bullet 3</label>
                <input v-model="data.bullet_3" class="form-control" placeholder=""
                       id="bullet_3" type="text" name="bullet_3"/>
            </div>

            {{-- bullet_4 --}}
            <div class="clearfix row-space-3">
                <label for="bullet_4" class="editor-label">Bullet 4</label>
                <input v-model="data.bullet_4" class="form-control" placeholder=""
                       id="bullet_4" type="text" name="bullet_4"/>
            </div>

            {{-- bullet_5 --}}
            <div class="clearfix row-space-3">
                <label for="bullet_5" class="editor-label">Bullet 5</label>
                <input v-model="data.bullet_5" class="form-control" placeholder=""
                       id="bullet_5" type="text" name="bullet_5"/>
            </div>

        </div>
    </div>
</div>


{{-- Boxes --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseBoxe1"
         aria-expanded="false" aria-controls="Boxe 1">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Boxe 1
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseBoxe1" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Boxe 1">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- box_1_image --}}
            <div class="clearfix row-space-3">
                <div class="col-sm-12 pointer row-space-top-3 row-space-3 full-bg-image img-rounded text-center image-library-button"
                     @click.prevent="this.$dispatch('open-image-library', this, $event)"
                     style="height: 150px;"
                     data-image-model="box_1_image"
                     v-bind:style="{'background-image': 'url(' + crop(data.box_1_image, 200, 100) + ')'}">
                    <span style="color: #FFFFFF; margin-top: 70px" class="inline-block">Box 1 image</span>
                </div>
            </div>

            {{-- box_1_title --}}
            <div class="clearfix row-space-3">
                <label for="box_1_title" class="editor-label">Title</label>
                <input v-model="data.box_1_title" class="form-control" placeholder=""
                       id="box_1_title" type="text" name="box_1_title"/>
            </div>

            {{-- box_1_text --}}
            <div class="clearfix row-space-3">
                <label for="box_1_text" class="editor-label">Text</label>
                <textarea v-model="data.box_1_text" class="form-control" placeholder=""
                       id="box_1_text" type="text" name="box_1_text"></textarea>
            </div>

        </div>
    </div>
</div>
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseBoxe2"
         aria-expanded="false" aria-controls="Boxe 2">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Boxe 2
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseBoxe2" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Boxe 2">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- box_2_image --}}
            <div class="clearfix row-space-3">
                <div class="col-sm-12 pointer row-space-top-3 row-space-3 full-bg-image img-rounded text-center image-library-button"
                     @click.prevent="this.$dispatch('open-image-library', this, $event)"
                     style="height: 150px;"
                     data-image-model="box_2_image"
                     v-bind:style="{'background-image': 'url(' + crop(data.box_2_image, 200, 100) + ')'}">
                    <span style="color: #FFFFFF; margin-top: 70px" class="inline-block">Box 2 image</span>
                </div>
            </div>

            {{-- box_2_title --}}
            <div class="clearfix row-space-3">
                <label for="box_2_title" class="editor-label">Title</label>
                <input v-model="data.box_2_title" class="form-control" placeholder=""
                       id="box_2_title" type="text" name="box_2_title"/>
            </div>

            {{-- box_2_text --}}
            <div class="clearfix row-space-3">
                <label for="box_2_text" class="editor-label">Text</label>
                <textarea v-model="data.box_2_text" class="form-control" placeholder=""
                       id="box_2_text" type="text" name="box_2_text"></textarea>
            </div>

        </div>
    </div>
</div>
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseBoxe3"
         aria-expanded="false" aria-controls="Boxe 3">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Boxe 3
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseBoxe3" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Boxe 3">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- box_3_image --}}
            <div class="clearfix row-space-3">
                <div class="col-sm-12 pointer row-space-top-3 row-space-3 full-bg-image img-rounded text-center image-library-button"
                     @click.prevent="this.$dispatch('open-image-library', this, $event)"
                     style="height: 150px;"
                     data-image-model="box_3_image"
                     v-bind:style="{'background-image': 'url(' + crop(data.box_3_image, 200, 100) + ')'}">
                    <span style="color: #FFFFFF; margin-top: 70px" class="inline-block">Box 3 image</span>
                </div>
            </div>

            {{-- box_3_title --}}
            <div class="clearfix row-space-3">
                <label for="box_3_title" class="editor-label">Title</label>
                <input v-model="data.box_3_title" class="form-control" placeholder=""
                       id="box_3_title" type="text" name="box_3_title"/>
            </div>

            {{-- box_3_text --}}
            <div class="clearfix row-space-3">
                <label for="box_3_text" class="editor-label">Text</label>
                <textarea v-model="data.box_3_text" class="form-control" placeholder=""
                       id="box_3_text" type="text" name="box_3_text"></textarea>
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
                <input v-model="data.button_text" class="form-control" placeholder="Download It Now"
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
                <input v-model="data.footer_title" class="form-control" placeholder="Enter your email"
                       id="footer_title" type="text" name="footer_title"/>
            </div>

        </div>
    </div>
</div>
