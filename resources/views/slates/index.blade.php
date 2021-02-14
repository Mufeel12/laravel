@extends('app')

@section('title', 'Slate pages')

@section('head.styles')
    <link rel="stylesheet" href="{!! asset('css/unslider.css') !!}">
@endsection

@section('modals')
    <!-- Large modal -->
    <div class="modal fade new-slate-modal in" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
         aria-hidden="true" style="height: 100%;">
        <div class="modal-close">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">&nbsp;</button>
        </div>
        <div class="modal-dialog modal-dialogal modal-md">
            <div class="modal-content">

                <!-- public -->

                <div class="row collapse in" id="public-project">
                    <div class="text-center text-uppercase row-space-4 row-space-top-11 text-light">Type a slate title
                    </div>
                    <form action="{!! route('slates.create') !!}" method="get" autocomplete="off">
                        <div class="row-space-top-1">
                            <input type="text" id="slateTitle" class="form-control special-modal title-input"
                                   name="slate_title"
                                   placeholder="Slate title"/>
                        </div>
                        <div class="clearfix">
                            <div class="row center-block relative" style="width: 350px">
                                <div class="row-space-top-2 row-space-4" id="slateTemplatesSlider">
                                    <ul>
                                        @foreach($templates as $template)
                                            <li data-id="{!! $template->id !!}">
                                                <div>
                                                    <img src="{!!  asset('img/slatePreview/' . $template->template . '.png') !!}"
                                                         class="img-responsive img-rounded" alt=""/>
                                                </div>
                                                <div class="row-space-top-2 text-center">
                                                    {{ $template->title }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="templateId" name="template_id" value="1"/>

                        <div class="clearfix">
                            <div class="text-center row-space-top-6">
                                <button class="btn btn-primary" type="submit" id="createProjectButton"
                                        style="width: 200px">Create
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if(count($slates))
        <div class="row">
            <div class="row row-space-5 col-md-12 row-space-top-4" style="background: #FFFFFF">
                <div class="col-md-12 row-space-top-2">
                    <div class="pull-right">
                        <button class="btn btn-primary pull-right inline-with-text-btn" data-toggle="modal"
                                data-target=".new-slate-modal">
                            Create new slate
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @foreach($slates as $slate)
            <div class="row-space-3 col-xs-12 col-sm-4" style="max-width: 330px;">
                <div class="row-space-2">
                    {{-- slate thumbnail --}}
                    <a href="{{ route('slates.edit', $slate->id) }}">
                        <img class="img-responsive" src="{!! $slate->thumbnail !!}" alt="{!! $slate->title !!}">
                    </a>
                </div>
                <div class="col-sm-12 row-space-1">

                    {{-- slate right menu --}}
                    <div class="pull-right row-space-top-2">
                        <div class="dropdown special-dropdown">
                            <button alt="" data-toggle="dropdown" aria-expanded="false" style="margin-top: 0"></button>
                            <ul class="dropdown-menu dropdown-center" style="right:-100% !important" role="menu">
                                <li role="presentation">
                                    <a role="menuitem" tabindex="-1" data-type="delete-slate"
                                       data-id="{!! $slate->id !!}" href="#">Delete slate</a>
                                    {!! Form::open([
                                        'method' => 'DELETE',
                                        'route' => ['slates.destroy'],
                                        'class' => 'hidden', 'style'=>"display: inline;"]) !!}
                                    <input type="hidden" name="id" value="{!! $slate->id !!}">
                                    {!! Form::submit('Delete slate', ['class' => 'no-styling hidden', 'id' => 'deleteSlate' . $slate->id]) !!}
                                    {!! Form::close() !!}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="slate-info">
                        {{-- slate title --}}
                        <div class="slate-title-wrap">
                            <h3 class="text-light video-element-title row-space-top-1">
                                <a href="{{ route('public.slate.show', $slate->id) }}" target="_blank"
                                   class="text-lightest new-tab-icon" title="Open slate page">
                                    <i class="material-icons new-tab-icon" style="font-size: 16px">open_in_new</i>
                                </a>
                                <a href="{{ route('slates.edit', $slate->id) }}" title="{!! $slate->title !!}"
                                   class="no-underline slate-title">
                                    {{ $slate->title }}
                                </a>
                            </h3>
                        </div>
                        {{-- updated at --}}
                        <div class="text-lightest updated-at" title="{!! $slate->updated_at->format('d.m.Y H:m') !!}">
                            Updated {!! time_elapsed_string($slate->updated_at) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        @include('slates.firstTimeScreen')
    @endif
@endsection


@section('footer')
    @parent
    <script type="text/javascript" src="{!! asset('js/slateIndex.js') !!}"></script>
    <script type="text/javascript" src="{!! asset('js/unslider.js') !!}"></script>
@endsection