@extends('app')

@section('title', 'Slate')

@section('content')
    <div class="dashboard-placeholder-block center-block dashboard-placeholder-blockall" style="max-width: 1124px">
        <img src="img/dashboard_placeholder_image.jpg" class="dashboard-placeholder-blockal">
        <div class="row-space-left-4 row-space-left-4al">
            <h4 class="row-space-inner-top-9">To use this feature you need projects with videos in place!</h4>
            <p class="row-space-top-4">Complete your first campaign, and just click the button.</p>
            <div class="row-space-top-6">
                <a class="btn btn-primary" href="{!! route('projects') !!}">Projects page</a>
            </div>
        </div>
    </div>
@endsection