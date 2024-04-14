@extends('web.unilever_tet.base')
@section('content')
    <style>
        .app-content{
            padding-bottom: 100px !important;
        }
        .app-content .design-info{
            display: none !important;
        }
        .app-content .top-banner {
            background: url("{{asset('web/unilever_tet/images/game1/top_game_1.png')}}") no-repeat bottom center;
            background-size: cover;
            padding-bottom: 100px;
        }
        .wrap-question .time p{
            background: linear-gradient(180deg, #FBB461 0%, #FFFFB0 100%);
            color: #000;
            border: 2px solid #FAD686;
        }
        .question-game{
            margin-top: -75px;
            padding: 0;
        }
        .wrap-question .list-ans{
            padding: 0;
        }
        .wrap-question .question h3{
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 24px;
            font-weight: bold;
            border-radius: 20px;
            border: 2px solid #FAD686;
            display: inline-block;
            padding: 3px 15px;
            z-index: 2;
            position: relative;
            background: #ce2129;
        }
        .wrap-question .question, .wrap-question .list-ans{
            background: none;
            box-shadow: none
        }
        .wrap-question .question .line{
            height: 2px;
            background: #FAD686;
            width: 100%;
            top: -25px;
            position: relative;
            z-index: 1;
            right: -15px;
        }
        .wrap-question .question .q{
            border-radius: 5px;
            background: url("{{asset('web/unilever_tet/images/bg_question.png')}}") no-repeat center;
            background-size: cover;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 19px;
            text-align: center;
        }
        .wrap-question .list-ans ul li{
            color: #fff;
            font-size: 14px;
            border: 0;
            padding: 10px 15px;
            padding-left: 70px;
        }
        .wrap-question .list-ans{
            padding-top: 0;
        }
        .wrap-question .list-ans ul li span{
            border-radius: 50%;
            border: 2px solid #FAD686;
            background: none;
            color: #fff;
            left: 15px;
        }
        .wrap-question .list-ans ul li.selected{
            color: #FFF88D;
        }
        .wrap-question .list-ans ul li.selected span{
            color: #000;
            background: #fff;
            box-shadow: 0 9px 13px 0 rgba(91,0,0,0.57);
            border: 2px solid #FAD686;
        }
        .question-result .wrap-question .list-ans ul li.selected{
            background: rgb(132 2 2 / 0.5);
        }
        .question-result .wrap-question .list-ans ul li.correct_answer{
            background: #5EB700;
            color: #fff;
        }
        .clock {
            text-align: center;
            position: relative;
            display: block;
            margin: 0 auto;
            /* transform: scale(0.7); */
            width: 300px;
            height: 300px;
        }
        .clock .text-content{
            position: absolute;
            width: 100%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #FFCD7D;
        }
        .clock .text-content h1 {
            font-weight: bold;
            font-size: 50px;
            color: #FFCD7D;
            position: relative;
        }
        .clock .text-content h1::after{
            content: '';
            height: 3px;
            width: 150px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            position: absolute;
            display: inline-block;
            background: #FFCD7D;
            border-radius: 2px;
        }
        .clock .text-content h1 b{
            font-size: 100px;
            color: #fff;
            font-weight: bold;
            -webkit-text-stroke-width: 2px;
            -webkit-text-stroke-color: #FFCD7D;
            margin-top: -20px;
            display: block
        }
        .clock .text-content h2 {
            font-size: 20px;
            color: #fff;
            font-weight: bold;
        }
        .clock svg {
            -webkit-transform:  rotate(-90deg);
            transform: rotate(-90deg);
        }
        .circle_animation {
            stroke-dasharray: 825; /* this value is the pixel circumference of the circle */
            stroke-dashoffset: 825;
            transition: all 1s linear;
        }
        .section-index.waiting{
            padding: 10px
        }
        .question-result .wrap-question .list-ans ul li.selected{
            background: rgb(132 2 2 / 0.5);
        }
        .question-result .wrap-question .list-ans ul li.correct_answer{
            background: #5EB700;
            color: #fff;
        }
    </style>

    <div class="section-index waiting {{ ($second <= 40) ? "d-none": "" }}">
        <div class="main-intro">
            <div class="clock">
                <div class="text-content">
                    <h1>Câu <br> <b>{{ $question->position }}</b></h1>
                    <h2>Chuẩn bị <span></span>s</h2>
                </div>
                <svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
                    <g>
                        <circle id="circle" class="circle_animation" r="130.98183" cy="153" cx="153" stroke-width="10" stroke="#FFCD7D" fill="none"/>
                    </g>
                </svg>
            </div>
        </div>
    </div>

    <div class="section-index question-game game3 {{ ($second > 40) ? "d-none": "" }}">
        <div class="main-intro">
            <div class="wrap-question">
                <div class="time">
                    <p class=""><span class="lb-time">Thời gian</span> <span class="timing"></span>s</p>
                </div>
                <div class="box-question">
                    <div class="question">
                        <h3 id="questionId" question-id="" num-answer="">Câu {{ $question->position }}</h3>
                        <div class="line"></div>
                        <p class="q">{{ $question->name }}</p>
                    </div>
                    <div class="list-ans">
                        <ul>
                            @foreach($answers as $answer)
                                <li data-ans="{{ $answer->answer }}" data-id="{{ $answer->id }}" style="cursor: pointer" class="item" data-correct="{{ $answer->correct }}">
                                    <p><span>{{ $answer->answer }}</span>{!! $answer->name !!}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{Helpers::asset('web/unilever_tet/js/cookie.js', true)}}" type="text/javascript"></script>
    <script>
        var total = "{{ $second }}";
        var totalAnswer = "{{ $totalAnswer }}";
        var uuid = "{{ $campaignCustomer->uuid }}";

        $(document).ready(function () {
            var numSecond = total - 50;
            if(parseInt(numSecond) <= 0){
                $('h2 span').text(0);
                $('.waiting').addClass('d-none');
                $('.question-game').removeClass('d-none');
            }
            $('h2 span').text(numSecond);

            if(total <= 10){
                if(totalAnswer == 5){
                    $('.lb-time').text('Vui lòng chờ');
                }else{
                    $('.lb-time').text('Câu hỏi tiếp theo sẽ bắt đầu sau');
                }
                $('.question-game').addClass('question-result');
                $( ".list-ans li" ).each(function() {
                    var correct = $(this).data('correct');
                    if(correct == 1){
                        if($(this).hasClass('selected')){
                            $(this).removeClass('selected');
                        }
                        $(this).addClass('correct_answer');
                    }
                });
            }
            var item_id = Cookies.get(uuid);
            if(typeof item_id !== 'undefined'){
                $( ".list-ans li" ).each(function() {
                    var id = $(this).data('id');
                    if(item_id == id && !$(this).hasClass('correct_answer')) {
                        $(this).addClass('selected');
                    }
                });
            }

            if(parseInt(total) <= 0){
                $('.timing').text(0);
            }

            var time = 16;
            var initialOffset = '825';
            var i = (time - numSecond);
            /* Need initial run as interval hasn't yet occured... */
            $('.circle_animation').css('stroke-dashoffset', initialOffset - (1 * (initialOffset / time)));

            var interval = setInterval(function () {
                if(parseInt(numSecond) < 0){
                    numSecond = 0;
                }
                $('h2 span').text(numSecond);
                if(numSecond <= 0){
                    $('.waiting').addClass('d-none');
                    $('.question-game').removeClass('d-none');
                    return false;
                }

                if (i == time || numSecond == 0) {
                    clearInterval(interval);
                }else{
                    if(numSecond < 0){
                        clearInterval(interval);
                    }
                }
                $('.circle_animation').css('stroke-dashoffset', initialOffset - ((i + 1) * (initialOffset / time)));
                i++;
                numSecond--;
            }, 1000);

            var interval1 = setInterval(function () {
                var numAnswer = parseInt(total) + 1;
                if(numAnswer >  10){
                    var timeAnswer = numAnswer - 11;
                    $('.timing').text(timeAnswer);
                }else{
                    $('.timing').text(numAnswer);
                }

                if(numAnswer <= 10){
                    if(totalAnswer == 5){
                        $('.lb-time').text('Vui lòng chờ');
                    }else{
                        $('.lb-time').text('Câu hỏi tiếp theo sẽ bắt đầu sau');
                    }
                    if(numAnswer == 10){
                        $('.question-game').addClass('question-result');
                        $( ".list-ans li" ).each(function() {
                            var correct = $(this).data('correct');
                            if(correct == 1){
                                if($(this).hasClass('selected')){
                                    $(this).removeClass('selected');
                                }
                                $(this).addClass('correct_answer');
                            }
                        });
                    }
                }
                if(total <= -1){
                    clearInterval(interval1);
                    processGame();
                };
                total--;
            }, 1000);

            $(document).on('click', '.list-ans li', function () {
                if(total > 10){
                    $('.list-ans li').removeClass('selected');
                    if($(this).hasClass('selected')){
                        $(this).removeClass('selected');
                        Cookies.remove(uuid);
                    }else{
                        $(this).addClass('selected');
                        var itemId = $(this).data('id');
                        Cookies.set(uuid, itemId);
                    }
                }
            });

            function processGame(){
                $('.loading').css({'display':'block','z-index':99999});
                var campaign_id = "{{ $campaignCustomer->campaign_id }}";
                var customer_id = "{{ $campaignCustomer->customer_id }}";
                var uuid = "{{ $campaignCustomer->uuid }}";
                var type = "{{ $campaignCustomer->type }}";
                var question = "{{ $question->id }}";
                var answer = Cookies.get(uuid);
                Cookies.remove(uuid);

                $.ajax({
                    url: "{{ route('utet.game.record') }}",
                    method: 'POST',
                    data:{campaign_id: campaign_id, customer_id: customer_id, uuid: uuid, type: type, item_id: answer, question: question},
                    async    : false,
                    headers  : {'X-CSRF-TOKEN': $('meta[name="csrfToken"]').attr('content')},
                    success: function(response){
                        if(response.status == 1){
                            setTimeout(function(){
                                window.location.href = response.link;
                            }, 2000);
                        }else{
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
        });
    </script>
@endsection
