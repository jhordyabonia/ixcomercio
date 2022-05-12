/**
 * CredomaticVisa for Magento JS component
 *
 * @category    CredomaticVisa
 * @package     CredomaticVisa
 * @author      Intcomex
 * @copyright   Intcomex (https://www.intcomex.com/)
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'Intcomex_CredomaticVisa/js/view/payment/cc-form',
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
                template: 'Intcomex_CredomaticVisa/payment/stripe-form',
            	  /*paymentPayload: {
                    nonce: null
                },
                creditCardInstallments: '',*/
	          },
	    
            getCode: function() {
                return 'credomaticvisa';
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
                var serviceUrl = url.build('credomaticvisa/custom/getorder');  
                var urlPaymentResponse = url.build('credomaticvisa/custom/paymentresponse');  
                var cuotas = $("#credomaticvisa_installments option:selected").val();
                var year = $("#credomaticvisa_expiration_yr option:selected").val();
                var month = $("#credomaticvisa_expiration option:selected").val();
                
                var zeroPad = (num, places) => String(num).padStart(places, '0');
                var monthFormatted = (month.slice(0, 2) >= 10) ? month.slice(0, 2) : zeroPad(month.slice(0, 2), 2);
                var number = $("#credomaticvisa_cc_number").val();
                var cvv_ = $("#credomaticvisa_cc_cid").val();
                var ccexp = monthFormatted + year.substring(2);
                $.post(serviceUrl,{cuotas:cuotas})
                .done(function(msg){
                    if ( !msg.error && msg.length != 0 ) {
                        jQuery('body').trigger('processStart');
                        jQuery('#credomaticvisaPaymentForm').attr('action', msg.url_gateway);
                        jQuery("#credomaticvisaPaymentForm input[name=key_id]").val(msg.key_id);
                        jQuery("#credomaticvisaPaymentForm input[name=amount]").val(msg.amount);
                        jQuery("#credomaticvisaPaymentForm input[name=time]").val(msg.time);
                        jQuery("#credomaticvisaPaymentForm input[name=hash]").val(msg.hash);
                        jQuery("#credomaticvisaPaymentForm input[name=orderid]").val(msg.orderid);
                        jQuery("#credomaticvisaPaymentForm input[name=processor_id]").val(msg.processor_id);
                        jQuery("#credomaticvisaPaymentForm input[name=firstname]").val(msg.firstname);
                        jQuery("#credomaticvisaPaymentForm input[name=lastname]").val(msg.lastname);
                        jQuery("#credomaticvisaPaymentForm input[name=email]").val(msg.email);
                        jQuery("#credomaticvisaPaymentForm input[name=phone]").val(msg.phone);
                        jQuery("#credomaticvisaPaymentForm input[name=street1]").val(msg.street1);
                        jQuery("#credomaticvisaPaymentForm input[name=street2]").val(msg.street2);
                        jQuery("#credomaticvisaPaymentForm input[name=cvv]").val(cvv_);
                        jQuery("#credomaticvisaPaymentForm input[name=ccnumber]").val(number);
                        jQuery("#credomaticvisaPaymentForm input[name=ccexp]").val(ccexp);
                        jQuery("#credomaticvisaPaymentForm input[name=redirect]").val(msg.redirect);
                        if(jQuery("#credomaticvisaPaymentForm input[name=redirect]").val() != ''){
                            setTimeout(function(){
                                jQuery('#credomaticPaymentForm').submit();
                            }, 500);
                        }else{
                            jQuery('#credomaticPaymentForm').submit();
                        }  
                    }else{
                        window.location.href = urlPaymentResponse;
                    }
                })
                .fail(function(msg){
                    window.location.href = urlPaymentResponse;
                })
                return false;
            },

            updateMenu: function () {
                let menu = $("#payment_methods_menu").find('ul');

                if (menu.length) {
                    let title_cont = $(".payment-method-title.credomaticvisa");
                    let title = $(title_cont).find('label.label span').text();
                    let code_payment = $(title_cont).find('input').attr('id');

                    title_cont.hide();

                    $(menu).prepend(
                        '<li role="presentation" class="payment-group-item debitcard active">' +
                            '<a class="link_option_credomaticvisa" style="padding-bottom: .5rem !important;" id="link-' + code_payment + '" data-code="' + code_payment + '">' + title + '</a>' +
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
