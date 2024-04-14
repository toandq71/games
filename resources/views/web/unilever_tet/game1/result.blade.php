@extends('web.unilever_tet.base')
@section('content')
<style>
    .app-content .top-banner {
        background: url("{{asset('web/unilever_tet/images/game1/top_game_1.png')}}") no-repeat bottom center;
        background-size: cover;
        padding-bottom: 100px;
    }
    /* .result-game:after{
        background: url("{{asset('web/unilever_tet/images/game1/bung.png')}}") no-repeat top center;
        background-size: 414px auto;
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
       display: block;
    } */
</style>

    <div class="section-index result-game game1 h-100 justify-content-center align-items-center">
        <div class="main-intro">
            <p>Chúc mừng bạn nhận được <br>phiếu quà tặng</p>
            @if($order->prize_type == 20)
                <img class="img-banner" src="{{asset('web/unilever_tet/images/giai-20k.png')}}">
            @elseif($order->prize_type == 50)
                <img class="img-banner" src="{{asset('web/unilever_tet/images/giai-50k.png')}}">
            @elseif($order->prize_type == 100)
                <img class="img-banner" src="{{asset('web/unilever_tet/images/giai-100k.png')}}">
            @endif

            <img class="img-congrat" style="margin-top: -45px; width: 200px" src="{{asset('web/unilever_tet/images/congrats_red.png')}}">
            <p>Phiếu quà tặng đã được gửi đến số điện thoại của bạn</p>
            <a href="{{ route('utet.game.choose', ['customer_id' => $order->customer_id, 'campaign_id' => $order->campaign_id]) }}" class="submit-text">Quay về trang chủ</a>
            <p class="hash_code"><small>Mã xác thực: {{ $order->hash_code }}</small></p>
        </div>
    </div>
@endsection
