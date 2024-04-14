@extends('web.unilever_tet.base')
@section('content')
<style>
    .app-content .top-banner {
        background: url("{{asset('web/unilever_tet/images/game1/top_game_1.png')}}") no-repeat bottom center;
        background-size: cover;
        padding-bottom: 100px;
    }
</style>
<div class="section-index cover-game game2 h-100 justify-content-center align-items-center">
    <div class="main-intro">
        <p class="btn-title">Luật chơi</p>
        <p>Trả lời 5 câu hỏi và <b>nhận ngay 1 vòng quay may mắn cho mỗi đáp án đúng.</b><br>
            Lưu ý: thời gian trả lời tối đa cho mỗi câu hỏi là 40s. </p>
        <img src="{{asset('web/unilever_tet/images/intro_game_3.png')}}">
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
