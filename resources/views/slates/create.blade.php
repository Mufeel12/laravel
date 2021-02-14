@extends('app')

@section('title', $title)

@section('content')
    <script type="text/javascript">
        window.mailerNicenames = {!! json_encode(\App\Mailer::getNicename()) !!};
    </script>
    <div id="slateEditor" class="row">

        <images></images>

        <div class="row">

            <!-- preview -->
            <div class="col-md-9 col-sm-8 col-xs-6 relative pull-left">

                <div id="previewWrapper">

                    <div class="clearfix" id="preview">
                        @include('slates.templates.' . $template->template)
                    </div>

                </div>

            </div>

            <div class="col-md-3 col-sm-4 col-xs-6 affix" id="editBar">
                <div class="col-md-12">

                    {{-- Back link --}}
                    <div class="clearfix row-space-1 row-space-top-2 back-to-projects-link">
                        <h4 class="text-uppercase pull-left">
                            <i class="cm-icon-arrow-back"></i>
                            <a class="no-underline" id="go-back-to-projects-page"
                               href="{!! route('slates.index') !!}">Back to Slates</a>
                        </h4>
                    </div>

                    {{-- title --}}
                    <div class="row-space-3">
                        <div v-if="slate_id" class="pull-right text-left" style="width: 37.5px; padding: 3px 10px">
                            <div class="dropdown special-dropdown">
                                <button id="videoEditDropdown" alt="" data-toggle="dropdown" aria-expanded="false" style="margin-top: 0"></button>
                                <ul class="dropdown-menu dropdown-center" style="right:-100% !important" role="menu" aria-labelledby="videoEditDropdown">
                                    <li role="presentation"><a role="menuitem" tabindex="-1" target="_blank" href="@{{ slate_url }}">Show</a></li>
                                    <li role="presentation" class="divider"></li>
                                    <li role="presentation">
                                        <a @click.prevent="deleteSlate()" role="menuitem" tabindex="-1" data-type="delete-slate" href="#">Delete slate</a>
                                        {!! Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['slates.destroy'],
                                            'class' => 'hidden', 'style'=>"display: inline;"]) !!}
                                        <input type="hidden" name="id" value="@{{ slate_id }}">
                                        {!! Form::submit('Delete slate', ['class' => 'no-styling hidden', 'id' => 'deleteSlateButton']) !!}
                                        {!! Form::close() !!}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div style="margin-right: 37.5px" id="videoEditorTitle" class="slateTitle">
                            <input @keydown.enter="saveSlateTitle()" id="slate-title-input" class="form-control hidden" type="text" v-model="slate_title">
                            <h3 @click="toggleSlateTitleEdit()" class="row-space-top-0 pointer">@{{ slate_title }}</h3>

                            <p class="smaller" style="overflow: hidden;" v-if="slate_url"><a class="text-lightest" href="@{{ slate_url }}" target="_blank">@{{ slate_url | truncate '35' }}</a></p>
                        </div>

                    </div>

                    {{-- show errors --}}
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true" style="width: 100%">

                        @include('slates.fields.' . $template->template)

                        <div class="row-space-top-4 row-space-4">
                            <input v-if="slate_id == false" @click="storeSlate()" type="button" value="Save" class="btn btn-primary"/>
                            <input v-else @click="updateSlate()" type="button" value="Update" class="btn btn-primary"/>

                            <img src="{!! asset('img/loading.gif') !!}" alt="loading" v-show="loading" class="row-space-top-0 row-space-left-2">
                        </div>

                    </div>

                </div>
            </div>

        </div>

    </div>

@endsection



@section('modals')

    @include('images.modal')

@endsection



@section('footer')
    @parent
    <script type="text/javascript">
        window.projects = {!! $projectsInJson or '[]' !!};
        window.project_id = {!! $video->project_id or 'false' !!};
        window.video_id = {!! $previewVideo->id or (isset($slate->video_id) ? $slate->video_id : 'false') !!};
        window.video_src = '{!! route('embedVideo', ['video_id' => '']) !!}/';
        window.template_id = '{!! Request::get('template_id', (isset($slate->template_id) ? $slate->template_id : 1)) !!}';
        window.slate_id = {!! $slate->id or 'false' !!};
        window.slate_title = '{!! Request::get('slate_title', (isset($slate->title) ? $slate->title : 'Unnamed slate')) !!}';
        window.showSlateUrl = '{!! route('public.slate.show', '') !!}';
        window.editVideoUrl = '{!! route('editVideo') !!}';
    </script>

    <script src="{!! asset('js/slateEditor.js') !!}"></script>
@endsection