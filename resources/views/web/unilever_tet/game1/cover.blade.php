@extends('web.unilever_tet.base')
@section('content')
    <div class="section-index cover-game game1 h-100 justify-content-center align-items-center">
        <div class="main-intro">
            <p class="btn-title">Luật chơi</p>
            <p><b>Chạm vào các quả bóng</b> chứa hình ảnh sản phẩm Unilever để tích lũy điểm và nhận ngay quà tặng siêu hấp dẫn.<br>
                Lưu ý: thời gian chơi tối đa là 45s.</p>
            <img src="{{asset('web/unilever_tet/images/intro_game_1.png')}}">
            <a href="javascript:void(0)" class="submit-text">Chơi ngay</a>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $(document).on('click', '.submit-text', function () {
                var campaign_id = "{{ $item->campaign_id }}";
                var customer_id = "{{ $item->customer_id }}";
                var uuid = "{{ $item->uuid }}";
                var type = "{{ $item->type }}";

                if(customer_id != '' && campaign_id != '' && uuid !='' && type != ''){
                    $('.loading').css({'display':'block','z-index':99999});

                    $.ajax({
                        url: "{{ route('utet.game.check') }}",
                        method: 'POST',
                        data:{customer_id: customer_id, campaign_id: campaign_id, uuid: uuid, type: type},
                        async    : false,
                        headers  : {'X-CSRF-TOKEN': $('meta[name="csrfToken"]').attr('content')},
                        success: function(response){
                            if(response.status == 1){
                                // redirect
                                if(response.link != ''){
                                    window.location.href = response.link;
                                }else{
                                    Swal.fire({
                                        type: 'error',
                                        html: 'Dịch vụ đang bận. Vui lòng thử lại!',
                                        showCloseButton: true,
                                        allowOutsideClick: false
                                    });
                                }
                            }else{
                                Swal.fire({
                                    type: 'error',
                                    html: response.message,
                                    showCloseButton: true,
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.value) {
                                        if($.inArray(parseInt(response.status), [2,3,4,5,6]) >= 0){
                                            $('.loading').css({'display':'block','z-index':99999});
                                            window.location.href = response.link;
                                        }
                                    }
                                });
                            }
                        }
                    }).done(function(){
                        $('.loading').css({'display':'none'});
                    });
                }else{
                    Swal.fire({
                        type: 'error',
                        html: 'Dịch vụ đang bận. Vui lòng thử lại!',
                        showCloseButton: true,
                        allowOutsideClick: false
                    });
                }
            })
        });
    </script>
@endsection
