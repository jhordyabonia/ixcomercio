/**
 * Credomatic for Magento JS component
 *
 * @category    Credomatic
 * @package     Credomatic
 * @author      Intcomex
 * @copyright   Intcomex (https://www.intcomex.com/)
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'Intcomex_Credomatic/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'mage/url'
    ],
    function (ko,Component, $,setPaymentMethodAction,quote,storage,url) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Intcomex_Credomatic/payment/stripe-form',
            	  /*paymentPayload: {
                    nonce: null
                },
                creditCardInstallments: '',*/
	          },
	    
            getCode: function() {
                return 'credomatic';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },
 
            afterPlaceOrder: function () {
                jQuery('body').trigger('processStart');
                var serviceUrl = url.build('credomatic/custom/getorder');  
                var urlPaymentResponse = url.build('credomatic/custom/paymentresponse');  
                var cuotas = $("#credomatic_installments option:selected").val();
                var year = $("#credomatic_expiration_yr option:selected").val();
                var month = $("#credomatic_expiration option:selected").val();
                var number = $("#credomatic_cc_number").val();
                var cvv_ = $("#credomatic_cc_cid").val();
                $.post(serviceUrl,{cuotas:cuotas,year:year,month:month,number:number,cvv_:cvv_})
                .done(function(msg){
                    jQuery('body').trigger('processStart');
                    
                    let url = msg.url_gateway;
                    let postForm =  '<form action="'+ url +'" metod="POST" id="credomaticForm"> <input name="type" value="sale"><input name="key_id" value="' + msg.key_id + '"><input name="amount" value="' + msg.amount + '"><input name="time" value="' + msg.time + '"><input name="hash" value="' + msg.hash + '"><input name="orderid" value="' + msg.orderid + '"><input name="processor_id" value="' + msg.processor_id + '"><input name="firstname" value="' + msg.firstname + '"><input name="lastname" value="' + msg.lastname + '"><input name="email" value="' + msg.email + '"><input name="phone" value="' + msg.phone + '"><input name="street1" value="' + msg.street1 + '"><input name="street2" value="' + msg.street2 + '"><input name="cvv" value="' + cvv_ + '"><input name="ccnumber" value="' + number + '"><input name="ccexp" value="' + msg.ccexp + '"><input name="redirect" value="' + msg.redirect + '"></form>';
                    jQuery('body').append(postForm);
                    jQuery('#credomaticForm').submit();
                })
                .fail(function(msg){
                    window.location.href = urlPaymentResponse;
                })
                return false;
            },

            updateMenu: function () {
                let menu = $("#payment_methods_menu").find('ul');

                if (menu.length) {
                    let title_cont = $(".payment-method-title.credomatic");
                    let title = $(title_cont).find('label.label span').text();
                    let code_payment = $(title_cont).find('input').attr('id');

                    title_cont.hide();

                    $(menu).prepend(
                        '<li role="presentation" class="payment-group-item debitcard active">' +
                            '<a class="link_option_credomatic" style="padding-bottom: .5rem !important;" id="link-' + code_payment + '" data-code="' + code_payment + '">' + title + '</a>' +
                            '<img style="padding-left: 1rem; padding-right: 2.5rem; padding-bottom: 1rem;" src="'+window.imgFranquiciasBAC+'" >' +
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
