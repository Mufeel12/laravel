<div class="clearfix relative" style="height: 20px;">
    <div class="moveABitLeft" style="position: absolute;">
        <input id="{!! $field['name'] !!}" type="hidden" class="form-control minicolors position-{!! $field['colorPickerPosition'] or 'left' !!}"
                   data-control="wheel"
                   data-targets='[{"name": "{!! $field["title"] !!}", "target": "#preview {!! $field["target"] !!}", "indicator": "#color{!! $field["name"] !!}", "attribute": "{!! $field["attribute"] !!}", "default": "{!! $field["defaultValue"] !!}", "inputName":"{!! $field["name"] !!}"}]'
                   value="{!! $slate->fields[$field['name']]['value'] or $field['defaultValue'] !!}"
                   size="7">

        <div style="top: 0; position: absolute;">
            <div class="colorpicker-buttons">
                <div class="colorpicker-button" id="color{!! $field['name'] !!}"
                     style="background: {!! $slate->fields[$field['name']]['value'] or $field['defaultValue'] !!}"></div>
                <img src="{!! asset('/img/colorpicker_down.png') !!}" style="right: -33px;position: absolute;top: 8px;"
                     alt="">
            </div>
        </div>
    </div>
</div>