<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Integration') }}</title>
    <!-- Scripts -->
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div id="app" class="text-center">
    @if($active)
        Authorization Successful
    @else
        Authorization Failed
    @endif
</div>
<script type="text/javascript">
    setTimeout(function () {
        window.opener.sessionStorage.setItem('integration-active', "{{ $active ? 'true' : 'false' }}");
        window.opener.sessionStorage.setItem('integration-id', "{{ $active ? $integration->id : '0' }}");
        window.close();
    }, 2000);
</script>
</body>
</html>
