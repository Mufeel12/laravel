<div class="col-sm-12 no-padding" id="dropZoneContainer{!! $field['name'] !!}"
     data-action="{!! route('uploadFile') !!}">
    <a class="no-underline block clearfix select-image-button slateImage" href="#" data-toggle="modal"
       data-target=".image-library-modal" data-element-target="#preview {!! $field['target'] !!}"
       data-target-input="#image-{!! $field['name'] !!}" data-type="open-image-library">
        <div class="image-library-box clearfix" id="dropZone{!! $field['name'] !!}">
            @if(isset($slate->fields[$field['name']]))
                <div style="width: 60px" class="pull-left">
                    <img src="{!! \Bkwld\Croppa\Facade::url($slate->fields[$field['name']]['value'], 50, 50) !!}" width="50" alt=""/>
                </div>
                <div style="margin-left: 60px">
                    <h5 class="text-uppercase text-primary row-space-1 row-space-top-0 image-title clearfix"
                        style="">
                        <span class="image-title-content">{!! $slate->fields[$field['name']]['image']->title or '' !!}</span>
                                    <span class="pull-left row-space-left-1">
                                        <span class="inline-block" data-type="remove-image" data-id=""><i
                                                    class="delete-icon icon"></i></span>
                                    </span>
                    </h5>

                    <p class="text-lightest row-space-top-0 row-space-0" style="font-size: 12px">Click here to <span class="text-primary">open uploader</span></p>
                </div>
            @else
                <div style="width: 60px" class="pull-left">
                    <img src="{!! asset('img/upload_box_cloud.png') !!}" width="50" alt=""/>
                </div>
                <div style="margin-left: 60px">
                    <h5 class="text-uppercase text-primary row-space-1 row-space-top-0 image-title clearfix"
                        style="font-weight: 500; letter-spacing: 1px; margin-right: 20px">
                        <span class="image-title-content">Upload {!! $field['title'] !!}</span>
                    </h5>

                    <p class="text-lightest row-space-top-0 row-space-0" style="font-size: 12px">For the
                        best result use an 8:10 image with transparent bg</p>
                </div>
            @endif
        </div>
    </a>
</div>
<input type="hidden" id="image-{!! $field['name'] !!}" name="{!! $field['name'] !!}"
       value="<?php
       if (isset($slate->fields[$field['name']]['value']))
           echo $slate->fields[$field['name']]['value'];
       elseif (isset($field['value']))
           echo $field['value'];
       ?>"/>