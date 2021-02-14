@if(isset($field['value']) && is_array($field['value']))
    <div class="clearfix relative" style="height: 20px;">
        <div class="" style="position: absolute;">
            <input id="{!! str_random() !!}" type="hidden" class="form-control minicolors position-{!! $field['colorPickerPosition'] or 'left' !!}"
                   data-control="wheel"
                   data-type="multiple"
                   data-targets='[<?php
                   if (isset($field['value']) && is_array($field['value']) && !empty($field['value'])) {
                   $i = 0;
                   foreach ($field['value'] as $key => $value) {
                   ?>{"name": "{!! $field['value'][$key]["title"] !!}", "target": "#preview {!! $field['value'][$key]["target"] !!}", "indicator": "#color{!! $field['value'][$key]["name"] !!}", "attribute": "{!! $field['value'][$key]["attribute"] !!}", "default": "{!! $slate->fields[$field['value'][$key]['name']]['value'] or $field['value'][$key]["defaultValue"] !!}", "inputName":"{!! $field['value'][$key]["name"] !!}"}<?php
                   if ($i == 0) {
                       echo ',';
                   }
                   $i++;
                   }
                   }

                   ?>]'
                   value=""
                   size="7">

            <div style="top: 0; position: absolute;">
                <div class="colorpicker-buttons" style="width: 43px">
                    <?php
                    if (isset($field['value']) && is_array($field['value']) && !empty($field['value'])) {
                    foreach ($field['value'] as $field) {
                    ?>
                    <div class="colorpicker-button" id="color{!! $field['name'] !!}"
                         style="background: {!! $slate->fields[$field['name']]['value'] or $field['defaultValue'] !!}"></div><?php
                    }
                    }
                    ?>
                    <img src="{!! asset('/img/colorpicker_down.png') !!}" style="right: 0;position: absolute;top: 8px;"
                         alt="">
                </div>
            </div>

        </div>
    </div>
@endif