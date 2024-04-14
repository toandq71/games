@extends('web.unilever_tet.base')
@section('content')
    <style>
        .app-content .top-banner {
            background: url("{{asset('web/unilever_tet/images/xoa.png')}}") no-repeat bottom center;
            background-size: cover;
            padding-bottom: 100px;
        }
        .result{
            text-align: center;
            margin-top: -75px;
        }
        .result .img_congrat{
            max-width: 80%;
            margin-bottom: 10px
        }
        .result .title, .result .note{
            color: #fff
        }
        .top-content{
            border-radius: 18.5px;
            background-color: #CE2129;
            display: inline-block;
            padding: 00px 20px;
            border: 2px solid #FFF88D;
            height: 37px;
            line-height: 37px;
            color: #fff;
        }
        .top-content p.pull-left{
            font-size: 12px;
            font-weight: bold;
            margin-right: 15px
        }
        .top-content p.pull-right{
            font-size: 25px;
            font-weight: bold;
            margin-left: 15px
        }
        .top-content p{
            margin: 0;
        }
        .top-content::after{
            clear: both;
            display: inline-block;
            content: ''
        }
        .wrap-tbl{
            margin-top: -20px;
            border-radius: 8px;
            border: 2px solid #FFF88D;
        }
        table{
            margin-bottom: 0 !important;
        }
        table thead{
            /* background: rgb(255 248 141 / 0.5); */
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 19px;
            text-align: center;
            border: 0;
            color: #fff;
            border-bottom: 2px solid #FFF88D;
        }

        table thead tr td{
            padding-top: 30px !important;

        }

        table tr td{
            border: 0 !important;
            padding: 5px  !important;
        }
        .table tbody tr td:first-of-type{
            color: #fff
        }
        .table tbody tr td:nth-child(2),
        .table tbody tr td:nth-child(3){
            color: #FFF88D;
            font-size: 16px;
            font-weight: bold;
        }
        .table tbody tr td:nth-child(2).wrong{
            color: #909090;
        }
        .table tbody tr:last-child{
            font-weight: bold;
        }
        .big_title{
            font-size: 52px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 61px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            color: #ffe293;
        }
        @media only screen and (max-width: 375px){
            .big_title{
                font-size: 40px;
            }
        }
        @media only screen and (max-width: 320px){
            .big_title{
                font-size: 36px;
            }
        }
    </style>

    <div class="section-index result">
        <div class="main-intro">
            @if($totalAnCorrect > 0)
                <img class="img_congrat" src="{{asset('web/unilever_tet/images/congrats_yellow.png')}}">
                <p class="title" style="font-weight: bold; font-size: 16px">Bạn đúng {{ $totalAnCorrect }} câu, bạn nhận được:</p>
            @else
                <p class="title" style="font-weight: bold; font-size: 16px">Rất tiếc! Bạn không đúng câu nào!</p>
                <p style="color: #fff; font-size: 15px">Unilever tặng bạn lượt quay may mắn</p>
            @endif
            <h1 class="big_title">{{ $campaignCustomer->total }} LƯỢT QUAY</h1>
            <div class="top-content">
                <p class="pull-left">TRẢ LỜI ĐÚNG</p>
                <p class="pull-right">{{ $totalAnCorrect }}<span>/{{ count($questions) }}</span></p>
            </div>
            <div class="wrap-tbl">
                <table class="table">
                    <thead>
                        <tr>
                            <td>CÂU HỎI</td>
                            <td>TRẢ LỜI</td>
                            <td>CÂU ĐÚNG</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($questions as $question)
                            <tr>
                                <td>{{ $question->position }}</td>
                                <td class="{{ (isset($answers[$question->id]) && $answers[$question->id]['correct'] == 0) ? 'wrong' : '' }}">{{ isset($answers[$question->id]) ? $answers[$question->id]['answer'] : '-' }}</td>
                                <td>{{ $question->answer }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <a href="{{ route('utet.game.spin',['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id, 'uuid' => $campaignCustomer->uuid]) }}" class="submit-text">Tham gia vòng quay</a>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            var correct = "{{ $totalAnCorrect }}";
            if(parseInt(correct) == 0){
                $('.top-banner').css({'background':'none'});
            }
        });
    </script>
@endsection
