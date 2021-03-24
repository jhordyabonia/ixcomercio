define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-list',
        'Magento_Checkout/js/action/get-totals',
        'jquery',
        'Magento_Checkout/js/model/full-screen-loader',
        'MercadoPago_Core/js/model/set-analytics-information',
        'mage/translate',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Checkout/js/model/cart/cache',
        'Magento_Checkout/js/model/payment/additional-validators',
        'MPcustom',
        'MPv1Ticket'
    ],
    function (Component, quote, paymentService, paymentMethodList, getTotalsAction, $, fullScreenLoader, setAnalyticsInformation, $t, defaultTotal, cartCache) {
        'use strict';

        return function (Component) {
            return Component.extend({
                initializeMethod: function () {

                },

                updateMenu: function () {

                    var menu = $("#payment_methods_menu").find('ul');

                    var title_cont = $(".payment-method-title.custom_ticket");

                    var title = $(title_cont).find('label.label span').text();
                    var input = $(title_cont).find('input');
                    var code_payment = $(input).attr('id');

                    $(menu).append('<li><a id="link-'+ code_payment+ '" data-code="'+ code_payment+ '">'+title+'</a></li>');


                    jQuery(document).on('click', `#payment_methods_menu ul li a#link-`+code_payment, function (event) {
                        var data = $(this).attr('data-code');
                        console.log("element click "+ data);

                        $('#'+data).trigger( "click" );
                    });


                }


            });
        }
    }
);
