/**
 * Pagalo for Magento JS component
 *
 * @category    Pagalo
 * @package     Pagalo
 * @author      Pagalo
 * @copyright   Pagalo (https://www.pagalo.com/)
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Pagalo/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Pagalo/payment/stripe-form',
                /*paymentPayload: {
                    nonce: null
                },
                creditCardInstallments: '',*/
	          },

            getCode: function() {
                return 'pagalo';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            updateMenu: function () {
                let menu = $("#payment_methods_menu").find('ul');

                if (menu.length) {
                    let title_cont = $(".payment-method-title.pagalo");
                    let title = $(title_cont).find('label.label span').text();
                    let code_payment = $(title_cont).find('input').attr('id');

                    title_cont.hide();

                    $(menu).prepend(
                        '<li role="presentation" class="payment-group-item debitcard active">' +
                            '<a style="padding-bottom: .5rem !important;" id="link-' + code_payment + '" data-code="' + code_payment + '">' + title + '</a>' +
                            '<img style="padding-left: 1rem; padding-right: 2.5rem; padding-bottom: 1rem;" src="'+window.imgFranquiciasPagalo+'" >' +
                        '</li>'
                    );

                    $('#' + code_payment).trigger("click");

                    $(document).on('click', `#payment_methods_menu ul li a#link-` + code_payment, function (event) {
                        let data = $(this).attr('data-code');
                        $('#' + data).trigger("click");
                        if ($(this).parent().hasClass('active')) {

                        } else {
                            $(menu).find('li.active').removeClass('active');
                            $(this).parent().addClass('active');
                        }
                    });
                }
            }
        });
    }
);
