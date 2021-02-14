<div class="clearfix">
    <div class="pull-right" v-show="editVideoUrl">
        <a href="@{{ editVideoUrl }}" target="_blank" class="text-lightest" title="Open in video editor" style="display: inline-block; margin-top: 11px"><i class="material-icons" style="font-size: 18px;">open_in_new</i></a>
    </div>
    <div style="margin-right: 30px">
        <select class="selectpicker video-picker form-control" data-live-search="true"
                v-selectpicker="video_id">
            <option value="">Select a video</option>

            <optgroup v-for="index in projects" label="@{{ index.project_title }}">
                <option v-for="video in index.videos"
                        title="@{{ video.title }}"
                        :selected="video_id == video.id"
                        data-content="<img title='@{{ video.title }}' src='@{{ crop(video.thumbnail, 70, 45) }}' /> <span title='@{{ video.title }}'>@{{ video.title }}</span>"
                        value="@{{ video.id }}">@{{ video.title | truncate '30' }}</option>
            </optgroup>
        </select>
    </div>
</div>