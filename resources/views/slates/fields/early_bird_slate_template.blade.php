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

            {{-- day --}}
            <div class="clearfix row-space-3">
                <label for="countdown_day_text" class="editor-label">Day</label>
                <input v-model="data.countdown_day_text" class="form-control" placeholder="Your day goes here"
                       id="countdown_day_text" type="text" name="countdown_day_text"/>
            </div>

            {{-- month --}}
            <div class="clearfix row-space-3">
                <label for="countdown_month" class="editor-label">Month</label>
                <input v-model="data.countdown_month" class="form-control" placeholder="Your month goes here"
                       id="countdown_month" type="text" name="countdown_month"/>
            </div>

            {{-- countdown_days --}}
            <div class="clearfix row-space-3">
                <label for="countdown_days" class="editor-label">Days</label>
                <input v-model="data.countdown_days" class="form-control" placeholder="Your days go here"
                       id="countdown_days" type="text" name="countdown_days"/>
            </div>

            {{-- countdown_time --}}
            <div class="clearfix row-space-3">
                <label for="countdown_time" class="editor-label">Time</label>
                <input v-model="data.countdown_time" class="form-control" placeholder="Your time goes here"
                       id="countdown_time" type="text" name="countdown_time"/>
            </div>

        </div>
    </div>
</div>












{{-- Email --}}
<div class="grey-dashed">
    <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapseEmail"
         aria-expanded="false" aria-controls="Email">
        <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
            <div class="row-space-left-2 pull-left video-editor-section-caption">
                Email
            </div>
            <div class="pull-right">
                <span class="collapsed-indicator inline-block"></span>
            </div>
        </div>
    </div>
</div>
<div id="collapseEmail" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="Email">
    <div class="row-space-top-3 clearfix">
        <div style="padding: 0 10px;">

            {{-- email_label --}}
            <div class="clearfix row-space-3">
                <label for="email_label" class="editor-label">Label</label>
                <input v-model="data.email_label" class="form-control" placeholder="Enter your email"
                       id="email_label" type="text" name="email_label"/>
            </div>

            {{-- email_button_text --}}
            <div class="clearfix row-space-3">
                <label for="countdown_month" class="editor-label">Button text</label>
                <input v-model="data.email_button_text" class="form-control" placeholder="Your month goes here"
                       id="email_button_text" type="text" name="email_button_text"/>
            </div>

            <script type="text/javascript">
                window.email_provider = '{!! $data['email_provider'] or '' !!}';
            </script>
            {{-- email provider --}}
            <div class="row-space-3">
                <div><label class="editor-label">Email provider:</label></div>
                <div class="email-provider-list"
                     data-name="email_provider"
                     data-value="{!! $data['email_provider'] or '' !!}">
                    <select class="selectpicker email-provider-picker form-control"
                            value="{!! $data['email_provider'] or '' !!}"
                            v-selectpicker="data.email_provider">
                        <option value="">Choose email list</option>
                        <optgroup v-for="provider in emailProviders"
                                  label="@{{ getName(provider.mailer) }}">
                            <option v-for="list in provider.lists"
                                    :selected="data.email_provider == list.id"
                                    value="@{{ provider.mailer }}_@{{ list.id }}">@{{ list.name }}</option>
                        </optgroup>
                    </select>
                </div>
            </div>

            {{-- redirect --}}
            <div class="clearfix row-space-3">
                <label for="countdown_month" class="editor-label">Redirect after filling out form to:</label>
                <input v-model="data.email_redirect" class="form-control" placeholder="Your redirect url"
                       id="email_redirect" type="text" name="email_redirect"/>
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
