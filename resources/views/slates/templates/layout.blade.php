@if(isset($standAlone))
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{!! $title or 'Motion CTA' !!}</title>
    <meta name="viewport" content="width=device-width; initial-scale=1.0">
    <meta id="_token" name="csrf-token" content="{!! csrf_token() !!}">

    @endif
    @yield('slateHead')
@if(isset($standAlone))
</head>
<body class="scroll-assist">
@endif
<div id="slate-page" class="main-container">
    @yield('slateContent')
</div>

@yield('slateFooter')

<script type="text/javascript">
    window.data = {!! (isset($data) ? json_encode($data) : json_encode([])) !!};
</script>

@if(isset($standAlone))
    <script src="{!! asset('js/slate.js') !!}"></script>
@endif

@if(isset($standAlone))
</body>
</html>
@endif