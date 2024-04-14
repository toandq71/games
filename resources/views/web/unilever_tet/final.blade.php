@extends('web.comfort.base')
@section('styles')
    <style>
        .main-result{
            margin: 10px 0 50px;
        }
        .main-result .box-top{
            background: #fff;
            padding: 20px 15px ;
            text-align: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            border-bottom: 1px dashed #00A6E4;
            position: relative;
        }
        .main-result .box-top::before, .main-result .box-top::after{
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            position: absolute;
            left: -15px;
            bottom: -15px;
            content: '';
            background: #00A2E1;
        }
        .main-result .box-top::after{
            left: initial;
            right: -15px;
        }
        .main-result .box-bottom{
            background: #fff;
            padding: 20px 15px ;
            text-align: center;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        .main-result .box-top h4,
        .main-result .box-bottom h4{
            color: #0069FF;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 24px;
            text-align: center;
        }
        .main-result .box-bottom h4.note{
            color: #000
        }
        .main-result .box-bottom h3{
            color: #F7B500;
            font-size: 40px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 47px;
            text-align: center;
        }
        .main-result .box-top img{
            max-width: 100%;
        }
        .main-result .box-top p{
            color: #F7B500;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 24px;
            text-align: center;
            margin: 5px 0;
        }
        .main-result .box-top p.name{
            color: #000;
        }
        .main-result .box-top p.score{
            color: #F7B500;
            font-size: 24px
        }
    </style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="section-index">
        <div class="main-result">
            <div class="box-top">
                <h4>Tổng kết điểm</h4>
                <div class="row">
                    @foreach($items as $item)
                        <div class="col-4">
                            <img src="{{asset('web/comfort/images/img_game_'.$item->type.'.png')}}">
                            <p class="name"><b>Game {{ $item->type }}</b></p>
                            <p class="score"><b>
                                    @if(in_array($item->type, [1,2]))
                                        {{ ($item->percent/10)*5 }}
                                    @else
                                        {{ $item->percent }}
                                    @endif
                                </b></p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="box-bottom">
                <h4>Tiền thưởng</h4>
                <h3>{{ Helpers::formatNumber($point * 1000, '.') }} VNĐ</h3>
                @if(!empty($order))
                    <h4 class="note">Quà tặng đã được gửi đến số điện thoại của bạn</h4>
                @else
                    <a href ="javascript:void(0)" class="submit-text" style="background-color: #0069FF !important;">Nhận thưởng</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).on('click', '.submit-text', function () {
            var customer_id = "{{ $items[0]->customer_id }}";
            var campaign_id = "{{ $items[0]->campaign_id }}";

            if(customer_id != '' && campaign_id != ''){
                $('.loading').css({'display':'block','z-index':99999});

                $.ajax({
                    url: "{{ route('comfort.game.voucher') }}",
                    method: 'POST',
                    data:{customer_id: customer_id, campaign_id: campaign_id},
                    async    : false,
                    headers  : {'X-CSRF-TOKEN': $('meta[name="csrfToken"]').attr('content')},
                    success: function(response){
                        setTimeout(function(){
                            $('.loading').css({'display':'none'});
                            if(response.status == 1){
                                location.reload();
                            }else{
                                if(response.link != ''){
                                    Swal.fire({
                                        type: 'error',
                                        html: response.message,
                                        showCloseButton: true,
                                        allowOutsideClick: false
                                    }).then((result) => {
                                        if (result.value) {
                                            window.location.href = response.link;
                                        }
                                    });
                                }else{
                                    Swal.fire({
                                        type: 'error',
                                        html: 'Dịch vụ đang bận. Yêu cầu của QK đang được xử lý!',
                                        showCloseButton: true,
                                        allowOutsideClick: false
                                    });
                                }
                            }
                        }, 3000);
                    }
                }).done(function(){
                });
            }else{
                Swal.fire({
                    type: 'error',
                    html: 'Dịch vụ đang bận. Yêu cầu của QK đang được xử lý!',
                    showCloseButton: true,
                    allowOutsideClick: false
                });
            }
        })
    </script>
@endsection
