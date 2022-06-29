define([
    'jquery'
], function ($) {
  "use strict";

    return function myscript()
    {   
        alert('oprimio el boton ');
        $('body').on('click', '.button-carousel', function() {
            console.log($(this).data("item"))
            switch ($(this).data("item")) {
                case 1:
                    $('.carousel1').removeClass("carousel-hidden").addClass('carousel-visible');
                    $('.carousel2').removeClass("carousel-visible").addClass('carousel-hidden');
                    $('.carousel3').removeClass("carousel-visible").addClass('carousel-hidden');
                    $('.carousel4').removeClass("carousel-visible").addClass('carousel-hidden');
                    break;
                case 2:
                    $('.carousel1').removeClass("carousel-visible").addClass('carousel-hidden');
                    $('.carousel2').removeClass("carousel-hidden").addClass('carousel-visible');
                    $('.carousel3').removeClass("carousel-visible").addClass('carousel-hidden');
                    $('.carousel4').removeClass("carousel-visible").addClass('carousel-hidden');
                    break;
                case 3:
                    $('.carousel1').removeClass("carousel-visible").addClass('carousel-hidden');
                    $('.carousel2').removeClass("carousel-visible").addClass('carousel-hidden');
                    $('.carousel3').removeClass("carousel-hidden").addClass('carousel-visible');
                    $('.carousel4').removeClass("carousel-visible").addClass('carousel-hidden');
                    break;
                case 4:
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