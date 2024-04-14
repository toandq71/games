@extends('web.unilever_tet.base')
@section('content')
    <style>
        .app-content .top-banner {
            background: url("{{asset('web/unilever_tet/images/game1/top_game_1.png')}}") no-repeat bottom center;
            background-size: cover;
            padding-bottom: 100px;
        }

        .play-game {
            margin-top: -85px;
            padding-bottom: 0;
        }

        .play-game .main-intro {
            padding: 10px 0 0;
            text-align: center;
            color: #fff;
            flex-grow: 1;
            flex-shrink: 1;
            flex-basis: auto;
            margin: auto;
            max-width: 380px;
        }

        .play-game .main-intro img {
            width: 100%;
        }

        .play-game .main-intro p {
            margin-bottom: 10px;
        }

        .play-game .main-intro .btn-title {
            display: inline-block;
            margin: 0 auto;
            box-sizing: border-box;
            border: 1px solid #FBB461;
            border-radius: 21px;
            padding: 5px 12px;
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin-bottom: 30px;
            border-radius: 19.5px;
            z-index: 999;
            position: relative;
            border-radius: 18px;
            background: linear-gradient(180deg, #FBB461 0%, #FFFFB0 100%)
        }

        .play-game .wrap-img {
            position: relative;
            width: 300px;
            max-width: 100%;
            margin: 0 auto;
        }

        .play-game .wrap-img .over_img {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(85, 85, 85, 0.3);
            max-width: 280px;
            width: 280px;
            border-radius: 10px
        }

        .play-game .progress {
            width: 100%;
            max-width: 350px;
            margin: 0 auto;
            margin-top: 10px;
            height: 33px;
            border-radius: 20px;
            padding: 3px;
        }

        .play-game .progress .progress-bar {
            border-radius: 20px;
            color: #F7B500;
            font-weight: bold;
            font-size: 16px;
            background: linear-gradient(270deg, #FFDC8E 0%, #EE3A26 47.91%, #00AAFA 76.87%, #006DFF 100%);
        }

        #falling {
            margin-top: -160px;
            padding: 0 15px;
        }

        #container {
            width: 100%;
            max-width: 350px;
            height: 480px;
            margin: auto;
            position: relative;
        }

        .leaf-1, .leaf-2, .leaf-3, .leaf-4, .leaf-5, .leaf-6,
        .leaf-7, .leaf-8, .leaf-9, .leaf-10, .leaf-11,
        .leaf-12, .leaf-13, .leaf-14, .leaf-15, .leaf-16,
        .leaf-17, .leaf-18, .leaf-19, .leaf-20, .leaf-21,
        .leaf-22, .leaf-23, .leaf-24, .leaf-25, .leaf-26,
        .leaf-27, .leaf-28, .leaf-29, .leaf-30, .leaf-31,
        .leaf-32, .leaf-33, .leaf-34, .leaf-35, .leaf-36,
        .leaf-37, .leaf-38, .leaf-39, .leaf-40
        {
            top: 0px;
            position: absolute;
            -webkit-animation: fall linear 3s infinite;
            animation-iteration-count: 1;
            width: 60px;
        }

        .leaf-1, .leaf-7, .leaf-12, .leaf-17, .leaf-22, .leaf-27, .leaf-32, .leaf-37, .leaf-5, .leaf-11 {
            left: -10px;
        }

        .leaf-2, .leaf-8, .leaf-13, .leaf-18, .leaf-23, .leaf-28, .leaf-33, .leaf-38, .leaf-6, .leaf-16{
            left: 70px;
            margin-top: -25px;
        }

        .leaf-3, .leaf-9, .leaf-14, .leaf-19, .leaf-24, .leaf-29, .leaf-34, .leaf-39, .leaf-21, .leaf-31 {
            right: 30%;
            margin-top: 25px;
        }

        .leaf-4, leaf-10, .leaf-15, .leaf-20, .leaf-25, .leaf-30, .leaf-35, .leaf-40, .leaf-26, .leaf-36 {
            right: 10px;
        }

        .design-info2 {
            position: relative;
        }

        .design-info2 img {
            max-width: 100%;
            -webkit-filter: blur(5px);
            -moz-filter: blur(5px);
            -o-filter: blur(5px);
            -ms-filter: blur(5px);
            filter: blur(10px);
        }

        .design-info2 a {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(85, 85, 85, 0.3);
            -webkit-filter: blur(5px);
            -moz-filter: blur(5px);
            -o-filter: blur(5px);
            -ms-filter: blur(5px);
            filter: blur(5px);
        }

        @-webkit-keyframes fall {
            0% {
                top: 000px;
                opacity: 1;
                -moz-opacity: 1;
                filter: alpha(opacity=10);
            }

            40% {
            }

            80% {
            }

            95% {
                opacity: 1;
                -moz-opacity: 1;
                filter: alpha(opacity=10);
            }

            100% {
                top: 420px;
                opacity: 0;
                -moz-opacity: 0;
                filter: alpha(opacity=0);
            }
        }

        .progress{
            position: relative;
            overflow: initial;
        }
        .img-giai{
            position: absolute;
            top: 100%;
            height: 50px;
            width: 78px !important;
        }
        .img-giai.giai-20{
            right: 70%;
        }
        .img-giai.giai-50{
            right: 40%;
        }
        .img-giai.giai-100{
            right: -5%;
        }
        .wrp-img{
            width: 60px;
            height: 60px;
            background: url("{{asset('web/unilever_tet/images/game1/bongbong.png')}}") no-repeat center center;
            background-size: cover;
            text-align: center;
            line-height: 60px;
        }
        .wrp-img img{
            width: initial !important;
            max-width: 90%;
            max-height: 90%;
            vertical-align: middle
        }
        .arrow-up {
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 5px solid #fff;
            position: absolute;
            top: 0;
            right: 18px;
        }
        .element-image{
            cursor: pointer;
        }
    </style>
    <div class="section-index play-game game1">
        <div class="main-intro">
            <p class="btn-title"><span class="timing"></span>s</p>
            <section id="falling">
                <div id="container">
                </div>
            </section>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: {{ $campaignCustomer->percent }}%;"
                     aria-valuenow="0" aria-valuemin="0"
                     aria-valuemax="100">{{ $campaignCustomer->percent }}%
                </div>
                <div class="img-giai giai-20">
                    <img class="" src="{{ asset('web/unilever_tet/images/giai-20k.png') }}">
                    <span class="arrow-up"></span>
                </div>
                <div class="img-giai giai-50">
                    <img class="" src="{{ asset('web/unilever_tet/images/giai-50k.png') }}">
                    <span class="arrow-up"></span>
                </div>
                <div class="img-giai giai-100">
                    <img class="" src="{{ asset('web/unilever_tet/images/giai-100k.png') }}">
                    <span class="arrow-up"></span>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{Helpers::asset('web/comfort/js/cookie.js', true)}}" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            var second = "{{ $second }}";
            var uuid = "{{ $campaignCustomer->uuid }}";
            var items = JSON.parse({!! json_encode($items->toJson()) !!});
            var percent = "{{ $campaignCustomer->percent }}";
            var domain = "{{ env('DOMAIN_UNILEVER_TET') }}";
            var arrItems = [];

            var listItem = Cookies.get(uuid);
            if(typeof listItem !== 'undefined'){
                if(listItem.length > 0){
                    arrItems = JSON.parse(listItem);
                    var p = 0;
                    for(var k = 0; k < arrItems.length; k++){
                        p += 1;
                    }
                    $('.progress-bar').attr('aria-valuenow', p);
                    $('.progress-bar').text(p + '%');
                    $('.progress-bar').css({'width': p + '%'});
                }
            }

            if (parseInt(second) < 0) {
                $('.timing').text(0);
            } else {
                $('.timing').text(second);
            }
            var interval = setInterval(function () {
                $('.element-image').each(function () {
                    var position = $(this).attr('data-element');
                    var element = document.getElementById("box-"+position);

                    // Code for Chrome, Safari and Opera
                    element.addEventListener("webkitAnimationEnd", () => {
                        $('.item-element-'+position).remove();
                    });
                    // Standard syntax
                    element.addEventListener("animationend", () => {
                        $('.item-element-'+position).remove();
                    });
                });

                var result1 = [];
                var result2 = [];
                var result3 = [];
                var i = Math.floor((Math.random() * 40) + 1);
                var j = Math.floor((Math.random() * 40) + 1);
                var l = Math.floor((Math.random() * 40) + 1);

                $('.element-image').each(function () {
                    if($('#box-'+i).hasClass('item-element-'+i)){
                        i = Math.floor((Math.random() * 40) + 1);
                    }
                    if($('#box-'+j).hasClass('item-element-'+j)){
                        j = Math.floor((Math.random() * 40) + 1);
                    }
                    if($('#box-'+l).hasClass('item-element-'+l)){
                        l = Math.floor((Math.random() * 40) + 1);
                    }
                });

                items.forEach(function (e) {
                    if (e.position == i) {
                        result1 = e;
                    }
                    if (e.position == j) {
                        result2 = e;
                    }
                    if (e.position == l) {
                        result3 = e;
                    }
                });

                var html1 = '<div id="box-'+result1.position+'" class="element-image item-element-'+result1.position+' leaf-' + result1.position + '" data-element="'+result1.position+'"><div class="wrp-img"><img src="' + (domain + '/' + result1.image) + '"></div></div>';
                var html2 = '<div id="box-'+result2.position+'" class="element-image item-element-'+result2.position+' leaf-' + result2.position + '" data-element="'+result2.position+'"><div class="wrp-img"><img src="' + (domain + '/' + result2.image) + '"></div></div>';
                var html3 = '<div id="box-'+result3.position+'" class="element-image item-element-'+result3.position+' leaf-' + result3.position + '" data-element="'+result3.position+'"><div class="wrp-img"><img src="' + (domain + '/' + result3.image) + '"></div></div>';

                $('#container').append(html1);
                $('#container').append(html2);
                $('#container').append(html3);

                if (second == 0) {
                    clearInterval(interval);
                    recordGame();
                }

                if (parseInt(second) < 0) {
                    second = 0;
                }
                if(second % 1 == 0){
                    $('.timing').text(second);
                }
                second = second - 0.5;
            }, 500);

            $(document).on('click', '.element-image', function () {
                var position = $(this).data('element');
                $('.item-element-'+position).remove();

                if(arrItems.length < 100){
                    items.forEach(function (e) {
                        if (e.position == position) {
                            arrItems.push(e);
                        }
                    });
                    Cookies.set(uuid, JSON.stringify(arrItems));

                    var p = 0;
                    for(var k = 0; k < arrItems.length; k++){
                        p += 1;
                    }
                    $('.progress-bar').attr('aria-valuenow', p);
                    $('.progress-bar').text(p + '%');
                    $('.progress-bar').css({'width': p + '%'});

                    if(p == 100){
                        recordGame();
                    }
                }
            });

            function recordGame() {
                var campaign_id = "{{ $campaignCustomer->campaign_id }}";
                var customer_id = "{{ $campaignCustomer->customer_id }}";
                var type = "{{ $campaignCustomer->type }}";
                var item_ids = '';

                if(arrItems.length > 0){
                    for(var j = 0; j <  arrItems.length; j++){
                        if(item_ids == ''){
                            item_ids = arrItems[j].id;
                        }else{
                            item_ids = item_ids+','+arrItems[j].id;
                        }
                    }
                }

                $('.loading').css({'display':'block','z-index':99999});
                Cookies.remove(uuid);

                $.ajax({
                    url: "{{ route('utet.game.record') }}",
                    method: 'POST',
                    data:{campaign_id: campaign_id, customer_id: customer_id, uuid: uuid, type: type, item_id: item_ids},
                    async    : false,
                    headers  : {'X-CSRF-TOKEN': $('meta[name="csrfToken"]').attr('content')},
                    success: function(response){
                        if(response.status == 1){
                            if(response.link != '') {
                                setTimeout(function(){
                                    window.location.href = response.link;
                                }, 2000);
                            }else{
                                $('.loading').css({'display':'none'});
                                location.reload();
                            }
                        }else{
                            $('.loading').css({'display':'none'});
                            Swal.fire({
                                type: 'error',
                                text: response.message,
                                showCloseButton: true,
                                allowOutsideClick: false
                            }).then(function(isConfirm) {
                                if (isConfirm) {
                                    if(response.link != ''){
                                        window.location.href = response.link;
                                    }
                                }
                            });
                        }
                    }
                }).done(function(){
                    $('.loading').css({'display':'none'});
                });
            }
        })
    </script>
@endsection
