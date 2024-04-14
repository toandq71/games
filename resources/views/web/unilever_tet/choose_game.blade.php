@extends('web.unilever_tet.base')
@section('content')
<style>
    .complete{
        color: #0091FF !important
    }
    .note{
        color: #F7B500; 
        font-size: 14px;
        font-weight: normal;
        letter-spacing: 0;
        line-height: 16px; 
        position: relative;
        padding-right: 100px;
    }
    .note #reloadGame{
        position: absolute;
        right: 0;
        top: 0;
        cursor: pointer;
        color: #fff;
        font-size: 14px;
        font-weight: bold;
        letter-spacing: 0;
        line-height: 18px;
        text-align: center;
        padding: 5px 20px;
        border-radius: 14px;
        background-color: #F7B500;
    }
</style>

<div class="section-index choose_game">
    <div class="wrap-content">
        <p class="note">Sau khi MC thông báo trò chơi bắt đầu, vui lòng nhấn <b>“Tải lại”</b> trình duyệt </b>
        <span id="reloadGame" src="{{asset('web/unilever_tet/images/reset.png')}}">Tải lại</span>
        </p>
        @if(count($items) > 0)
            @foreach($items as $item)
                @if($item->type == 1)
                    <div class="box-item game-1 {{ ($onOffGame1 == 1 && $item->state == 0) ? 'active' : '' }}">
                        @if($onOffGame1 == 1 && $item->state == 0)
                            <img src="{{asset('web/unilever_tet/images/intro_1_active.png')}}">
                        @else
                            <img src="{{asset('web/unilever_tet/images/intro_1.png')}}">
                        @endif
                        @if($item->state == 0)
                            @if($onOffGame1 == 1)
                                <p class="status">Trò chơi đang diễn ra</p>
                                <a href="{{ route('utet.game.intro', ['customer_id' => $item->customer_id, 'campaign_id' => $item->campaign_id, 'uuid' => $item->uuid]) }}" class="cover_link"></a>
                            @else
                                    @if(!empty($item->start_time) && !empty($item->end_time) && strtotime($item->end_time) < strtotime(date('Y-m-d H:i:s',time())))
                                        <a href="{{ route('utet.game.result', ['customer_id' => $item->customer_id, 'campaign_id' => $item->campaign_id, 'uuid' => $item->uuid]) }}" class="cover_link"></a>
                                    @else
                                        <p class="status">Trò chơi chưa diễn ra</p>
                                    @endif
                            @endif
                        @else
                            <p class="status">Trò chơi đã hoàn thành</p>
                        @endif
                    </div>
                @elseif($item->type ==2)
                    <div class="box-item game-2 {{ ($onOffGame2 == 1 && $item->state == 0) ? 'active' : '' }}">
                        @if($onOffGame2 == 1 && $item->state == 0)
                            <img src="{{asset('web/unilever_tet/images/intro_2_active.png')}}">
                        @else
                            <img src="{{asset('web/unilever_tet/images/intro_2.png')}}">
                        @endif
                        @if($item->state == 0)
                            @if($onOffGame2 == 1)
                                <p class="status">Trò chơi đang diễn ra</p>
                                <a href="{{ route('utet.game.intro', ['customer_id' => $item->customer_id, 'campaign_id' => $item->campaign_id, 'uuid' => $item->uuid]) }}" class="cover_link"></a>
                            @else
                                <p class="status">Trò chơi chưa diễn ra</p>
                            @endif
                        @else
                            <p class="status">Trò chơi đã hoàn thành</p>
                        @endif
                    </div>
                @elseif($item->type == 3)
                    <div class="box-item game-3 {{ ($onOffGame3 == 1 && $item->state == 0) ? 'active' : '' }}">
                        @if($onOffGame3 == 1 && $item->state == 0)
                            <img src="{{asset('web/unilever_tet/images/intro_3_active.png')}}">
                        @else
                            <img src="{{asset('web/unilever_tet/images/intro_3.png')}}">
                        @endif

                        @if($item->state == 0)
                            @if($onOffGame3 == 1)
                                <p class="status">Trò chơi đang diễn ra</p>
                                <a href="{{ route('utet.game.intro', ['customer_id' => $item->customer_id, 'campaign_id' => $item->campaign_id, 'uuid' => $item->uuid]) }}" class="cover_link"></a>
                            @else
                                <p class="status">Trò chơi chưa diễn ra</p>
                            @endif
                        @else
                            <p class="status">Trò chơi đã hoàn thành</p>
                        @endif
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</div>
@endsection

@section('scripts')
    <script>
        var url  = "{{ route('utet.game.choose', ['customer_id' => $items[0]->customer_id, 'campaign_id' => $items[0]->campaign_id]) }}";
        $(document).ready(function () {
            $(document).on('click', '#reloadGame', function () {
                window.location.href = url;
            });
        });
    </script>
@endsection

