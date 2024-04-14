@extends('web.unilever_tet.base')
@section('styles')
<link href="{{ asset('web/unilever_tet/css/spin.css') }}" rel="stylesheet" type="text/css"/>
@endsection
@section('content')
<div class="content-wrapper">
    <section class="flexbox-container page-luckydraw-dt">
        <div class="align-items-center justify-content-center p-0">
            <div class="border-grey border-lighten-3 px-1 m-0">
                <div class="card-content" style="padding: 15px 10px">
                    <p class="count_spin">Đã quay: {{ ($campaignCustomer->used.'/'.$campaignCustomer->total)}} lượt</p>
                    <div class="wrap_canvas">
                        <canvas id="canvas" width="800" height="800" data-responsiveMinWidth="200" data-responsiveScaleHeight="true">
                            <p style="color: white" align="center">Sorry, your browser doesn't support canvas. Please try another.</p>
                        </canvas>
                        <div class="content-text-center">
                            <p>Nhấn vào</p>
                            <button class="btn_quay2 btnplay-game" data-index="" onClick="startSpin();" data-href="#">&nbsp;</button>
                            <p>để tiếp tục</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@section('scripts')
    <script type="text/javascript">
        var rem          = "{{ $campaignCustomer->remaining }}";
        pnumber          = 1;
        var segments     = {!! $prizes !!};
        var countSegment = "{{ $prizes->count() }}";
        var imgWhell     = "{{ asset("/web/unilever_tet/images/vong-quay.png") }}";
        var uuid         = "{{ $campaignCustomer->uuid }}";;
        var customer     = "{{ $campaignCustomer->customer_id }}";
        var urlProccess  = "{{ route('utet.process.spin') }}";
        var campaignId   = "{{ $campaign->id }}";
        var urlList      = "{{ route('utet.game.choose', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]) }}";

        $(document).ready(function(){
            $('.btn_quay2').click(function(){
                $(this).attr('disabled','disabled');
            });
        });
    </script>

    <script type="text/javascript" src="{{Helpers::asset('web/unilever_tet/js/TweenMax.min.js')}}"></script>
    <script type="text/javascript" src="{{Helpers::asset('web/unilever_tet/js/Winwheel.js')}}"></script>
    <script type="text/javascript" src="{{Helpers::asset('web/unilever_tet/js/app.system.js')}}"></script>
    <script type="text/javascript" src="{{ Helpers::asset('web/unilever_tet/js/custom-spin.js') }}"></script>
@endsection
