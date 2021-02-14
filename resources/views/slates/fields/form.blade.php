@foreach($sections as $section)

    <div class="grey-dashed">
        <div class="clearfix pointer collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#section-{!! $section['id'] !!}" aria-expanded="false" aria-controls="section-{!! $section['id'] !!}">
            <div class="row-space-top-3 row-space-3 text-uppercase clearfix" style="height: 20px;">
                <div class="pull-left row-space-right-1">
                    <i class="material-icons" style="margin-top: -2px; opacity: 0.7">{!! $section['icon'] or '' !!}</i>
                </div>
                <div class="pull-left video-editor-section-caption">
                    {!! $section['section_title'] !!}
                </div>
                <div class="pull-right">
                    <span class="collapsed-indicator inline-block"></span>
                </div>
            </div>
        </div>
    </div>

    <div id="section-{!! $section['id'] !!}" class="panel-collapse collapse grey-dashed" role="panel" aria-labelledby="section-{!! $section['id'] !!}">
        <div class="row-space-top-3 row-space-3 clearfix">
            @foreach($section['fields'] as $field)
                <div class="clearfix row-space-2">
                    <div class="col-sm-12 {!! $field['attributes']['class'] or '' !!}" style="{!! $field['attributes']['style'] or '' !!}">
                        <div>
                            <label class="editor-label" for="{!! $field['name'] or '' !!}">{!! $field['title'] or '' !!}</label>
                        </div>
                        @include('slates.fields.' . $field['type'])
                    </div>

                    @if(isset($field['children']))
                        @foreach($field['children'] as $field)
                            <div class="{!! $field['attributes']['class'] or '' !!}" style="{!! $field['attributes']['style'] or '' !!}">
                                @include('slates.fields.' . $field['type'])
                            </div>
                        @endforeach
                    @endif

                </div>
            @endforeach
        </div>
    </div>
@endforeach

