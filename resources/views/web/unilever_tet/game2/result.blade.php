@extends('web.unilever_tet.base')
@section('content')
    <style>
        .app-content{
            /* padding-bottom: 0 !important; */
        }
        .app-content .design-info{
            /* display: none !important; */
        }
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
        table thead{
            background: rgb(255 248 141 / 0.5);
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 19px;
            text-align: center;
            border: 0;
        }
        table tr td{
            border: 0 !important;
        }
        .table-striped tbody tr{
            background: #900000;
        }
        .table-striped tbody tr:nth-of-type(odd){
            background: rgb(144 0 0 / 0.5);
        }
        .table-striped tbody tr td:first-of-type{
            color: #fff
        }
        .table-striped tbody tr td:last-of-type{
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 0;
            line-height: 26px;
            text-align: center;
            color: #FFF88D;
        }
        .table-striped tbody tr td:nth-child(2),
        .table-striped tbody tr td:nth-child(3){
            color: #FFF88D;
            font-size: 16px;
            font-weight: bold;
        }
        .table-striped tbody tr td:nth-child(2).wrong{
            color: #909090;
        }
        .table-striped tbody tr:last-child{
            font-weight: bold;
        }
        .table-striped tbody tr:last-child td:last-of-type{
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 0;
            line-height: 26px;
            text-align: center;
            color: #FFF88D;
        }
        .modal-footer{
              border: 0;
              padding: 0;
              text-align: center;
              justify-content: center;
              z-index: 10;
              margin-top: -125px;
          }
          .modal-footer .submit-text{
            max-width: 85%;
          }
        .modal-content{
            background: none;
            border: 0;
            max-width: 380px;
            margin: 0 auto;
        }
        .modal-body{
            text-align: center;
            text-align: center;
            background-image: url("{{asset('web/unilever_tet/images/wallet.png')}}"), url("{{asset('web/unilever_tet/images/xoa.png')}}");
            background-position: top 10px center, top center;
            background-repeat: no-repeat, no-repeat;
            background-size: 100% auto, 100% auto;
            padding: 60px 15px 270px;
        }
        .prize_text{
            color: #000;
            text-transform: uppercase;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 19px;
            text-align: center;
            padding-left: 10px;
            padding-right: 10px;
        }
        .prize_name{
            color: #E02020;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 19px;
            text-align: center;
            margin-bottom: 5px;
        }
        .prize_value{
            color: #E02020;
            font-size: 70px;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 82px;
            text-align: center;
            text-shadow: 0 12px 17px#DBA100;
        }

        @media only screen and (max-width: 375px){
            .prize_text, .prize_name{
                font-size: 15px;
                margin-bottom: 5px;
                padding-left: 10px;
                padding-right: 10px;
            }
            .prize_value{
                font-size: 60px;
                line-height: 65px;
            }
            .modal-body{
                padding: 50px 15px 260px;
            }
            .modal-footer{
                margin-top: -100px
            }
        }
        @media only screen and (max-width: 320px){
            .prize_text, .prize_name{
                font-size: 13px;
                margin-bottom: 5px;
                padding-left: 5px;
                padding-right: 5px;
            }
            .prize_value{
                font-size: 50px;
                line-height: 55px;
            }
            .modal-body{
                padding: 50px 15px 200px;
            }
            .modal-footer{
                margin-top: -90px
            }
        }
    </style>

    <div class="section-index result">
        <div class="main-intro">
            <img class="img_congrat" src="{{asset('web/unilever_tet/images/congrats_yellow.png')}}">
            <p class="title">Bạn nhận được các phần quà sau</p>
            <div class="row">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <td>Câu hỏi</td>
                            <td>Trả lời</td>
                            <td>Câu đúng</td>
                            <td>Quà</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($questions as $question)
                            <tr>
                                <td>{{ $question->position }}</td>
                                <td class="{{ (isset($answers[$question->id]) && $answers[$question->id]['correct'] == 0) ? 'wrong' : '' }}">{{ isset($answers[$question->id]) ? $answers[$question->id]['answer'] : '-' }}</td>
                                <td>{{ $question->answer }}</td>
                                <td>{{ (isset($answers[$question->id]) && $answers[$question->id]['correct'] == 1) ? Helpers::formatNumber(($question->point * 1000), '.').'Đ' : '-'}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
            <p class="note">Phần quà đã được gửi đến số điện thoại của bạn. Vui lòng kiểm tra tin nhắn</p>
            <a href="{{ route('utet.game.choose', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]) }}" class="submit-text">Quay về trang chủ</a>
        </div>
    </div>
@endsection
