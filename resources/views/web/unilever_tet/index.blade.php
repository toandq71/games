@extends('web.unilever_tet.base')
@section('styles')
   <style>
       .disabled{
           background: #ababab !important;
       }
       .submit-text{
           background: #F7B500;
       }
   </style>
@endsection

@section('content')
<div class="content-wrapper">

    <div class="section-index">
        <form class="form" id="formSave" method="post" action="">
            {{ csrf_field() }}
            @if(!empty($err) &&  $err->has('invalid'))
                <div class="alert bg-danger alert-dismissible mb-2" style="color: #fff; background: #b4755a !important;">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    {{ $err->first('invalid') }}
                </div>
            @endif
            @include('web.components.alert')
            <div class="form-group">
                <p>Vui lòng cung cấp số điện thoại đăng nhập chính xác.</p>
            </div>
            <div class="form-group">
                <label class="lbInput title-text">Số điện thoại</label>
                <input type="tel" class="form-control input-text {{ (!empty($err) && $err->has('phone')) ? 'has-error': '' }}"
                    placeholder="Nhập số điện thoại" id="phoneNumber" name="phone" minlength="10" maxlength="10" onblur="validateForm(1)" onpaste="validateForm()"
                    value="{{isset($phone) && !empty($phone) ? $phone : ''}}"/>
                @if (!empty($err) && $err->has('phone'))
                    <span class="help-block">{{ $err->first('phone') }}</span>
                @endif
            </div>
            <div class="form-group">
                <a href="javascript:void(0)" class="submit-text disabled">Tiếp tục</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
    <script type="text/javascript">
        var validAll = false;
        var submitBtn = $(".submit-text");

        function validateNumber(event) {
            event = window.event;
            var key = window.event ? event.keyCode : event.which;

            if (event.keyCode === 8 || event.keyCode === 46
                || event.keyCode === 37 || event.keyCode === 39) {
                return true;
            }
            else if ( key < 48 || key > 57 ) {
                return false;
            }
            else{
                return true;
            }
        };

        function validateForm(flag = 0) {
            if(flag == 1){
                var phone = $('#phoneNumber').val();
                if($.trim(phone) != ''){
                    var regExPhone = /^(03|09|08|07|05)[0-9]{8}$/;
                    if (!regExPhone.test(phone)) {
                        var msg = '<p>Số điện thoại không đúng định dạng</p>';
                        $("#phoneNumber").addClass('has-error');

                        Swal.fire({
                            type: 'error',
                            html: msg,
                            showCloseButton: true,
                            allowOutsideClick: false
                        });
                        $('.submit-text').addClass('disabled');
                    }else{
                        $('.submit-text').removeClass('disabled');
                    }
                }
            }
        }

        $(document).ready(function(){
            $("#phoneNumber").keypress(validateNumber);
            $('#phoneNumber').change(function() {
                var phone = $('#phoneNumber').val();
                var regExPhone = /^(03|09|08|07|05)[0-9]{8}$/;

                if (regExPhone.test(phone) && validAll) {
                    $('.submit-text').removeClass('disabled');
                }
            });

            $('.input-text').change(function() {
                var isValid = true;
                var name = $(this).attr('name');
                var flag = true;
                var message = '';

                if(name == 'phone'){
                    var phone = $(this).val();
                    var regExPhone = /^(03|09|08|07|05)[0-9]{8}$/;
                    if (!regExPhone.test(phone)) {
                        isValid = false;

                        if(phone != ''){
                            message = 'Số điện thoại không đúng định dạng';
                            flag = false;
                        }
                    }
                }
                if ($.trim($(this).val()) == '') {
                    flag = false;
                }
                if(isValid){
                    $('.submit-text').removeClass('disabled');
                } else{
                    $('.submit-text').addClass('disabled');
                }

                if(!flag){
                    $('.submit-text').addClass('disabled');
                    if(message != ''){
                        Swal.fire({
                            type: 'error',
                            html: message,
                            showCloseButton: true,
                            allowOutsideClick: false
                        });
                    }
                }
            });

            $('.input-text').keyup(function () {
                var isValid = true;
                var name = $(this).attr('name');

                if (name == 'phone') {
                    var regExPhone = /^(03|09|08|07|05)[0-9]{8}$/;
                    if (!regExPhone.test($.trim($(this).val()))) {
                        isValid = false;
                    }
                }
                if (isValid) {
                    $('.submit-text').removeClass('disabled');
                } else {
                    $('.submit-text').addClass('disabled');
                }
            });

            submitBtn.on("click", function (e) {
                e.preventDefault();
                var phone = $.trim($("#phoneNumber").val());

                var msg = '';
                var flag = true;

                if (phone === '') {
                    msg += '<p>Vui lòng nhập số điện thoại</p>';
                    flag = false;
                    $("#phoneNumber").addClass('has-error');
                } else {
                    var regExPhone = /^(03|09|08|07|05)[0-9]{8}$/;
                    if (!regExPhone.test(phone)) {
                        msg += '<p>Số điện thoại không đúng định dạng</p>';
                        flag = false;
                        $("#phoneNumber").addClass('has-error');
                    }
                }

                if (flag) {
                    $('.loading').css({'display':'block','z-index':99999});
                    $("#formSave").submit();
                } else {
                    Swal.fire({
                        type: 'error',
                        html: msg,
                        showCloseButton: true,
                        allowOutsideClick: false
                    });
                    $('.submit-text').addClass('disabled');
                }
            });
        })
    </script>
@endsection
