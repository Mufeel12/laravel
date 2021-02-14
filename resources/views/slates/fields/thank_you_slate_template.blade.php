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








{{-- Image --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseImage"
         aria-expanded="false" aria-controls="Image">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Image
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseImage" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Email">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- image --}}
            <div class="clearfix row-space-3">
                <div class="col-sm-12 pointer row-space-top-3 row-space-3 full-bg-image img-rounded text-center image-library-button"
                     @click.prevent="this.$dispatch('open-image-library', this, $event)"
                     style="height: 150px;"
                     data-image-model="image"
                     v-bind:style="{'background-image': 'url(' + crop(data.image, 200, 100) + ')'}">
                    <span style="color: #FFFFFF; margin-top: 70px" class="inline-block">Image</span>
                </div>
            </div>

        </div>
    </div>
</div>






{{-- Comments --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseComments"
         aria-expanded="false" aria-controls="Footer">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Comments
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseComments" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Comments">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- comments_url --}}
            <div class="clearfix row-space-3">
                <label for="comments_url" class="editor-label">Comments url</label>
                <input v-model="data.comments_url" class="form-control" placeholder="Enter comments url"
                       id="comments_url" type="text" name="comments_url"/>
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
