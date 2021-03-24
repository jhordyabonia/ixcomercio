define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/iframe',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-list',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/action/place-order',
        'Magento_Customer/js/model/customer',
        'MercadoPago_Core/js/model/set-analytics-information',
        'mage/translate',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Checkout/js/model/cart/cache',
        'MPcustom',
        'MPanalytics',
        'MPv1'
    ],
    function ($, Component, quote, paymentService, paymentMethodList, getTotalsAction, fullScreenLoader, additionalValidators,
              setPaymentInformationAction, placeOrderAction, customer, setAnalyticsInformation, $t, defaultTotal, cartCache) {
        'use strict';


        return function (Component) {
            return Component.extend({

                updateMenu: function () {
                    console.log("method custom");

                    var menu = $("#payment_methods_menu").find('ul');

                    var title_cont = $(".payment-method-title.custom_method");

                    var title = $(title_cont).find('label.label span').text();
                    var input = $(title_cont).find('input');
                    var code_payment = $(input).attr('id');


                    //$('#payment_methods_menu ul li:first').appendTo('<li><a data-code="'+ code_payment +'">'+title+'</a></li>');
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
