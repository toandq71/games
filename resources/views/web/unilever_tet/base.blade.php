<!DOCTYPE html>
<html lang="en" data-textdirection="ltr">
<head>
    <title>Unilever Têt 2021</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
    <meta name="csrfToken" content="{{ csrf_token() }}">
    <meta name="google" value="notranslate">
    <meta name="webRoot" content="{{asset('')}}">

    <meta name="description" content="">
    <meta name="author" content="">
    @yield('metadata')
    <!-- Social Sharing Info -->
    <meta property="og:title" content="" />
    <meta property="og:description" content="" />
    <meta property="og:url" content="" />
    <meta property="og:image" content="" />
    <meta property="og:type"  content="website" />
    <meta property="og:image:width" content="600" />
    <meta property="og:image:height" content="315" />

    <meta content="width=device-width, initial-scale=1.0, height=device-height" name="viewport"/>
    <link href="{{Helpers::asset('web/vendor/fonts/font-awesome/css/font-awesome.min.css', true)}}" rel="stylesheet" type="text/css"/>
    <link href="{{Helpers::asset('web/vendor/bootstrap41/css/bootstrap.min.css', true)}}" rel="stylesheet" type="text/css"/>

    <link href="{{ asset('web/unilever_tet/css/style.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="shortcut icon" href="{{Helpers::asset('favicon.gif') }}">
    <link rel="apple-touch-icon" href="{{Helpers::asset('favicon.gif') }}">
    @yield('styles')
    <script src="{{ Helpers::asset('web/vendor/js/jquery.min.js', true) }}" type="text/javascript"></script>
</head>
<body class="" onload="noBack();" onpageshow="if (event.persisted) noBack();" onunload="">
    <div class="app-content">
        <div class="top-banner" style="position: relative;">
            <img class="img-text-banner img1" src="{{ asset('web/unilever_tet/images/text-banner.png') }}" alt=""/>
        </div>
        
        @yield('content')
            @php
                $routeName = \Route::currentRouteName();
            @endphp
            
        <div class="design-info @if(isset($route) && in_array($route, ['index']))  @else small @endif">
            <img class="banner-footer" src="{{asset('web/unilever_tet/images/banner-footer.png')}}">
        </div>
    </div>
    @include('web.components.loading')
    <style>
        .list-inline-item a{
            cursor: pointer;
        }
        body.swal2-height-auto{
            height: 100% !important;
        }
    </style>

    <script src="{{ Helpers::asset('web/vendor/bootstrap41/js/bootstrap.min.js', true) }}" type="text/javascript"></script>
    <script src="{{Helpers::asset('web/vendor/js/sweetalert.min.js', true)}}" type="text/javascript"></script>
    @yield('scripts')
    <script type="text/javascript">
        window.history.forward();
        function noBack() { window.history.forward(); }
    </script>
</body>
</html>
