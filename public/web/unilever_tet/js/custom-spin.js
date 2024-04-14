if ($('.page-luckydraw-dt').length > 0) {
    // Create new wheel object specifying the parameters at creation time.
    var theWheel = new Winwheel({
        'responsive': true,  // This wheel is responsive!
        'pointerAngle': 0,//set arow check winner bottom
        'numSegments' : parseInt(countSegment),         // Specify number of segments.
        'outerRadius' : 150,       // Set outer radius so wheel fits inside the background.
        'drawMode'    : 'image',   // drawMode must be set to image.
        'drawText'    : false,     // Need to set this true if want code-drawn text on image wheels.
        'segments'    : segments,
        'urlProccess' : urlProccess,
        'animation':                   // Specify the animation to use.
            {
                'type': 'spinToStop',
                'easing': 'Power4.easeOut',
                'duration': 15,     // Duration in seconds.
                'spins': 25,     // Number of complete spins.
                'callbackFinished': alertPrize,
            }
    });
    if(urlProccess !== undefined){
        theWheel.urlProccess = urlProccess;
    }
    // Create new image object in memory.
    var loadedImg = new Image();

    // Create callback to execute once the image has finished loading.
    loadedImg.onload = function () {
        theWheel.wheelImage = loadedImg;    // Make wheelImage equal the loaded image object.
        theWheel.draw();                    // Also call draw function to render the wheel.
    };

    // Set the image source, once complete this will trigger the onLoad callback (above).
    loadedImg.src = imgWhell;


    // Vars used by the code in this page to do power controls.
    var wheelPower = 0;
    var wheelSpinning = false;
    var webRoot = $('meta[name=webRoot]').attr("content");

    var urlResult = '';
    // -----------------------------------------------------------------
    // Called by the onClick of the canvas, starts the spinning.
    var segment  = 0;
    function startSpin() {
        if(parseInt(rem) == 0){
            Swal.fire({
                type: 'error',
                text: 'Link quay đã hết lượt. Vui lòng kiểm tra lại',
                showCloseButton: true,
                allowOutsideClick: false
            }).then(function(isConfirm) {
                if (isConfirm) {
                    window.location.href = urlList;
                }
            });;

            return false;
        }
        $('.loading').css({'display': 'block'});
        if (wheelSpinning) {
            return false;
        }
        var msg      = "";
        var flag     = false;
        var link = '';
        $.ajax({
            url      : theWheel.urlProccess,
            type     : "post",
            data     : {
                uuid       : uuid,
                campaign_id : campaignId,
                customer_id: customer
            },
            async    : false,
            headers  : {'X-CSRF-TOKEN': $('meta[name="csrfToken"]').attr('content')},
            success  : function (response) {
                $('.loading').css({'display': 'none'});

                if (typeof response !== "object") {
                    flag = false;
                } else {
                    if (response.status == 1) {
                        segment = response.data.position;
                        urlResult = response.link;
                        flag = true;
                    } else{
                        msg = response.message;
                        link = response.link;
                    }
                }

            }
        }).done (function () {
            $('.loading').css({'display': 'none'});
        });

        if (!flag) {
            if (msg != "") {
                if(link != ''){
                    Swal.fire({
                        type: 'error',
                        text: msg,
                        showCloseButton: true,
                        allowOutsideClick: false
                    }).then(function(isConfirm) {
                        if (isConfirm) {
                            window.location.href = link;
                        }
                    });
                }else{
                    Swal.fire({
                        type: 'error',
                        text: msg,
                        showCloseButton: true,
                        allowOutsideClick: false
                    }).then(function(isConfirm) {
                        if (isConfirm) {
                            window.location.reload(true);
                        }
                    });
                }
            } else {
                var message = "Dịch vụ đang bận, vui lòng thử lại.";
                Swal.fire({
                    type: 'error',
                    text: message,
                    showCloseButton: true,
                    allowOutsideClick: false
                }).then(function(isConfirm) {
                    if (isConfirm) {
                        window.location.reload(true);
                    }
                });
            }
            return false;
        }
        // segment = 5;
        wheelSpinning = true;
        theWheel.stopAnimation(false);
        theWheel.rotationAngle = 0;

        if (segment != "") {
            calculatePrize(segment);
        }
        // Start animation.
        theWheel.startAnimation();
    }

    // -----------------------------------------------------------------
    // Called when the spinning has finished.
    function alertPrize(indicatedSegment) {
        wheelSpinning = false;
        // thuc hien chuyen trang
        if(urlResult != ''){
            setTimeout(function () {
                window.location = urlResult;
            },200);
        }
    }
    // Function with formula to work out stopAngle before spinning animation.
    function calculatePrize(segment) {
        segment = parseInt(segment);

        var random = getRandomArbitrary(40, 60);
        var value = random/100;

        var stopAt = theWheel.getRandomForSegment(segment, value);
        theWheel.animation.stopAngle = stopAt;
    }

    function getRandomArbitrary(min, max) {
        return Math.random() * (max - min) + min;
    }
}
