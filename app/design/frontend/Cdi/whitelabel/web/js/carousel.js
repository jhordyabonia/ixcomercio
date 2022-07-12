define([
    'jquery'
], function ($) {
    "use strict";

    return function myscript(total)
    {   
        $('body').on('click', '.button-carousel', function() {
            var selected = $(this).data("item");

            if ($(this).data("item") == 0) {
                var before =  document.getElementsByClassName("button-selected");
                var selectInt = parseInt(before[0].dataset['item']);
                if (selectInt < total) {
                    selected = selectInt + 1;
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
        });  
    }
});