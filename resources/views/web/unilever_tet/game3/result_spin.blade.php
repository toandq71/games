@extends('web.unilever_tet.base')
@section('styles')
<link href="{{ asset('web/unilever_tet/css/spin.css') }}" rel="stylesheet" type="text/css"/>
<style>
    .btn-continue, .btn-continue:hover, .btn-continue:focus, .btn-continue:active{
        display: block;
        height: 50px;
        border-radius: 7px;
        background-color: #0091FF;
        line-height: 50px;
        font-size: 16px;
        font-weight: bold;
        letter-spacing: 0;
        text-align: center;
        text-transform: uppercase;
        text-decoration: none;
        max-width: 90%;
        margin: 0 auto;
    }
    .text_happy{
        font-size: 13px !important;
        padding-left: 25px;
        padding-right: 25px;
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <section class="flexbox-container page-luckydraw-dt">
        <div class="align-items-center justify-content-center p-0">
            <div class="border-grey border-lighten-3 px-1 m-0">
                <div class="card-content">
                    <div class="wrap_gift {{ ($order->prize_type > 0) ? 'hasgift' : '' }}">
                        <div class="main-gift">
                            <img class="gift" src="{{asset($prize->image)}}">
                        </div>
                        @if($order->prize_type > 0)
                            <img class="congrats" src="{{asset('web/unilever_tet/images/congrats_yellow.png')}}">
                        @endif
                        <div class="gift-content">
                            {!! $prize->text_popup !!}
                            <p class="hash_code">Mã xác thực: {{ $order->hash_code }}</p>
                        </div>
                    </div>
                    @if($campaignCustomer->remaining > 0)
                        <div class="form-group text-center">
                            <a href="{{ route('utet.game.spin', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id, 'uuid' => $campaignCustomer->uuid]) }}" class="submit-text btn-continue">Tiếp tục quay</a>
                        </div>
                    @else
                        <div class="form-group text-center">
                            <a href="{{ route('utet.game.choose', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]) }}" class="submit-text btn-continue">Về trang chủ</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
