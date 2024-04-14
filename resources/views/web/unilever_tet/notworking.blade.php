@extends('web.unilever_tet.base')
@section('styles')
   <style>
       .notworking{
           background: #9B0000;
           color: #fff;
           text-align: center;
           padding: 20px 10px;
           border-radius: 5px;
       }
       .notworking p{
        font-size: 20px;
        font-weight: normal;
        letter-spacing: 0;
        line-height: 1.5;
        text-align: center;
        color: #fff;
        margin-bottom: 20px
       }
       .notworking p .time{
        color: #F7B500;
       }
       .notworking a, .notworking a:hover, .notworking a:focus, .notworking a:visited{
           color: #fff;
           font-size: 14px;
           text-decoration: underline
       }
   </style>
@endsection

@section('content')
<div class="content-wrapper">

    <div class="section-index">
        <div class="notworking">
            <p>Chương trình sẽ bắt đầu vào <br><b class="time">4:00 PM ngày 28/11</b></p>
            <p>Xin vui lòng quay lại khi chương trình bắt đầu</p>
            <a href="{{route('utet.index')}}">Về Trang Chủ</a>
        </div>
        
    </div>
</div>
@endsection

@section('scripts')
    
@endsection
