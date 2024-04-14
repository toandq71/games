<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <title>Got It Campaign</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="csrfToken" content="{{ csrf_token() }}">
    <meta name="webRoot" content="{{asset('')}}">
    @yield('metadata')
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="{{Helpers::asset('web/fonts/font-awesome/css/font-awesome.min.css', true)}}" rel="stylesheet" type="text/css"/>
    <link href="{{Helpers::asset('web/css/bootstrap.min.css', true)}}" rel="stylesheet" type="text/css"/>
    <link href="{{Helpers::asset('web/fonts/stylesheet.css', true)}}" rel="stylesheet" type="text/css"/>
    <link href="{{Helpers::asset('web/css/bootstrap-datetimepicker.css', true)}}" rel="stylesheet" type="text/css"/>
    <link href="{{Helpers::asset('web/fonts/stylesheet.css?v=1.1')}}" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->

    <link rel="icon" type="image/x-icon" href="{{asset('/favicon.ico')}}">

    <style>
      .top_logo{
        text-align: center;
      }
      .top_logo img{
        max-width: 150px !important;
        max-height: 60px;
        margin-bottom: 30px;
      }
      h3{
        color: #06377B;
        font-weight: bold;
        margin-bottom: 20px
      }
      .app-content{
          margin: 0 auto;
          background: url(../../web/images/bg_1.png) !important;
          background-size: cover !important;
          background-repeat: no-repeat;
          background-size: 100% auto;
          max-width: 414px;
          height: auto;
          background-position: top center !important;
          height: 100vh;
          padding-top: 50px;
      }

    .notfound-404 h1 {
        font-size: 70px;
        margin: 0px;
        font-weight: 900;
        -webkit-background-clip: text;
        background-size: cover;
        background-position: center;
    }

      .top_logo .logo{

      }
      </style>
  </head>
  <body>
@php
    $message = "<h3>404 - Trang không tồn tại</h3>
                <p>Trang bạn đang tìm kiếm có thể đã bị xóa hoặc tạm thời không có.</p>";
@endphp
<section class="app-content" style="">
  <div class="top_logo">
      <img class="logo" src="{{asset('web/images/logo.png')}}">
  </div>
  <div class="page-content d-flex col-md-8 col-md-offset-2">
    <div class="d-flex justify-content-center align-items-center text-center w-100"
    style="flex-direction: column;align-items: center;
    justify-content: center;">
      <div class="flex-column w-100">
        <div class="notfound-404">
            <h1>Oops!</h1>
        </div>
        {!! $message !!}
      </div>
    </div>
  </div>
</section>
</body>
</html>

