require([
    'jquery', ,
], function ($) {
    'use strict';
    $(document).ready(function () {
        setTimeout(function () {
            if (window.checkout.lesspercent) {
                let varDiv = $('.div-free-shipping-message .free-shipping-icon');
                let varDivClass = document.getElementsByClassName('free-shipping-icon');

                if (varDiv.length > 0) {
                    varDiv.each(function (index) {
                        if (window.checkout.lesspercent < 100) {
                            varDivClass[index].style.cssText = '--width-bar: ' + window.checkout.lesspercent + '%';
                        } else {
                            varDivClass[index].style.cssText = '--width-bar: 100%';
                        }
                    })
                } else {
                    if (window.checkout.lesspercent < 100) {
                        varDivClass.style.cssText = '--width-bar: ' + window.checkout.lesspercent + '%';
                    } else {
                        varDivClass.style.cssText = '--width-bar: 100%';
                    }
                }
            }
        }, 8000);
    });
});