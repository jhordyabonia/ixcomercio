define([
    'jquery'
], function ($) {
    "use strict";

    return function myscript(total)
    {   
        function changeCarousel(itemC, orientation) {
        var selected = itemC;

        if (itemC == 0) {
            var before =  document.getElementsByClassName("button-selected");
            var selectInt = parseInt(before[0].dataset['item']);
            if (selectInt < total && orientation) {
                selected = selectInt + 1;
            }else{
                if (!orientation) {
                    selected = selectInt - 1;
                }
            }
        }

        switch (selected) {
            case 1:
                $('.dot2').removeClass("dot-selected").addClass('dot');
                $('.dot3').removeClass("dot-selected").addClass('dot');
                $('.dot4').removeClass("dot-selected").addClass('dot');
                $('.dot1').removeClass("dot").addClass('dot-selected');
                $('.button-carousel1').addClass('button-selected');
                $('.button-carousel2').removeClass('button-selected');
                $('.button-carousel3').removeClass('button-selected');
                $('.button-carousel4').removeClass('button-selected');
                $('.carousel1').removeClass("carousel-hidden").addClass('carousel-visible');
                $('.carousel2').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel3').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel4').removeClass("carousel-visible").addClass('carousel-hidden');
                break;
            case 2:
                $('.dot1').removeClass("dot-selected").addClass('dot');
                $('.dot3').removeClass("dot-selected").addClass('dot');
                $('.dot4').removeClass("dot-selected").addClass('dot');
                $('.dot2').removeClass("dot").addClass('dot-selected');
                $('.button-carousel2').addClass('button-selected');
                $('.button-carousel1').removeClass('button-selected');
                $('.button-carousel3').removeClass('button-selected');
                $('.button-carousel4').removeClass('button-selected');
                $('.carousel1').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel2').removeClass("carousel-hidden").addClass('carousel-visible');
                $('.carousel3').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel4').removeClass("carousel-visible").addClass('carousel-hidden');
                break;
            case 3:
                $('.dot2').removeClass("dot-selected").addClass('dot');
                $('.dot1').removeClass("dot-selected").addClass('dot');
                $('.dot4').removeClass("dot-selected").addClass('dot');
                $('.dot3').removeClass("dot").addClass('dot-selected');
                $('.button-carousel3').addClass('button-selected');
                $('.button-carousel1').removeClass('button-selected');
                $('.button-carousel2').removeClass('button-selected');
                $('.button-carousel4').removeClass('button-selected');
                $('.carousel1').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel2').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel3').removeClass("carousel-hidden").addClass('carousel-visible');
                $('.carousel4').removeClass("carousel-visible").addClass('carousel-hidden');
                break;
            case 4:
                $('.dot2').removeClass("dot-selected").addClass('dot');
                $('.dot3').removeClass("dot-selected").addClass('dot');
                $('.dot1').removeClass("dot-selected").addClass('dot');
                $('.dot4').removeClass("dot").addClass('dot-selected');
                $('.button-carousel4').addClass('button-selected');
                $('.button-carousel1').removeClass('button-selected');
                $('.button-carousel2').removeClass('button-selected');
                $('.button-carousel3').removeClass('button-selected');
                $('.carousel1').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel2').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel3').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel4').removeClass("carousel-hidden").addClass('carousel-visible');
                break;

            default:
                break;
        }
        }

        if($(window).width() < 991){
            $('#product-comparison').addClass('comparison-movil');
            $('.dot1').removeClass("dot").addClass('dot-selected');
            $('.button-carousel1').addClass('button-selected');
        }else{
            $('#product-comparison').removeClass('comparison-movil');
        }
        
        $('body').on('click', '.button-carousel', function() {
            changeCarousel($(this).data("item"), false);
        });

        $(window).resize(function(){
            if($(window).width() < 991){
                $('#product-comparison').addClass('comparison-movil');
                $('.dot1').removeClass("dot").addClass('dot-selected');
                $('.dot2').removeClass("dot-selected").addClass('dot');
                $('.dot3').removeClass("dot-selected").addClass('dot');
                $('.dot4').removeClass("dot-selected").addClass('dot');
                $('.button-carousel1').addClass('button-selected');
                $('.button-carousel2').removeClass('button-selected');
                $('.button-carousel3').removeClass('button-selected');
                $('.button-carousel4').removeClass('button-selected');
                $('.carousel1').removeClass("carousel-hidden").addClass('carousel-visible');
                $('.carousel2').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel3').removeClass("carousel-visible").addClass('carousel-hidden');
                $('.carousel4').removeClass("carousel-visible").addClass('carousel-hidden');
            }else{
                $('#product-comparison').removeClass('comparison-movil');
                $('.carousel1').removeClass("carousel-hidden").addClass('carousel-visible');
                $('.carousel2').removeClass("carousel-hidden").addClass('carousel-visible');
                $('.carousel3').removeClass("carousel-hidden").addClass('carousel-visible');
                $('.carousel4').removeClass("carousel-hidden").addClass('carousel-visible');
            }
        });

        $(document).ready(function(){
            $(".comparison-movil").on("swiperight",function(){
                changeCarousel(0, false);
            });

            $(".comparison-movil").on("swipeleft",function(){
                changeCarousel(0, true);
            });
        });
    }
});