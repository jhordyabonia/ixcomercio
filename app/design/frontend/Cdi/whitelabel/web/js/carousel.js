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