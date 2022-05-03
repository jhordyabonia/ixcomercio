/**
 * CredomaticMasterCard for Magento JS component
 *
 * @category    CredomaticMasterCard
 * @package     CredomaticMasterCard
 * @author      Intcomex
 * @copyright   Intcomex (https://www.intcomex.com/)
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'Intcomex_CredomaticMasterCard/js/view/payment/cc-form',
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
                template: 'Intcomex_CredomaticMasterCard/payment/stripe-form',
            	  /*paymentPayload: {
                    nonce: null
                },
                creditCardInstallments: '',*/
	          },
	    
            getCode: function() {
                return 'credomaticmastercard';
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
                var serviceUrl = url.build('credomaticmastercard/custom/getorder');  
                var urlPaymentResponse = url.build('credomaticmastercard/custom/paymentresponse');  
                var cuotas = $("#credomaticmastercard_installments option:selected").val();
                var year = $("#credomaticmastercard_expiration_yr option:selected").val();
                var month = $("#credomaticmastercard_expiration option:selected").val();
                
                var zeroPad = (num, places) => String(num).padStart(places, '0');
                var monthFormatted = (month.slice(0, 2) >= 10) ? month.slice(0, 2) : zeroPad(month.slice(0, 2), 2);
                var number = $("#credomaticmastercard_cc_number").val();
                var cvv_ = $("#credomaticmastercard_cc_cid").val();
                var ccexp = monthFormatted + year.substring(2);
                $.post(serviceUrl,{cuotas:cuotas})
                .done(function(msg){
                    if ( !msg.error && msg.length != 0 ) {
                        jQuery('body').trigger('processStart');
                        jQuery('#credomaticMastercardPaymentForm').attr('action', msg.url_gateway);
                        jQuery("#credomaticMastercardPaymentForm input[name=key_id]").val(msg.key_id);
                        jQuery("#credomaticMastercardPaymentForm input[name=amount]").val(msg.amount);
                        jQuery("#credomaticMastercardPaymentForm input[name=time]").val(msg.time);
                        jQuery("#credomaticMastercardPaymentForm input[name=hash]").val(msg.hash);
                        jQuery("#credomaticMastercardPaymentForm input[name=orderid]").val(msg.orderid);
                        jQuery("#credomaticMastercardPaymentForm input[name=processor_id]").val(msg.processor_id);
                        jQuery("#credomaticMastercardPaymentForm input[name=firstname]").val(msg.firstname);
                        jQuery("#credomaticMastercardPaymentForm input[name=lastname]").val(msg.lastname);
                        jQuery("#credomaticMastercardPaymentForm input[name=email]").val(msg.email);
                        jQuery("#credomaticMastercardPaymentForm input[name=phone]").val(msg.phone);
                        jQuery("#credomaticMastercardPaymentForm input[name=street1]").val(msg.street1);
                        jQuery("#credomaticMastercardPaymentForm input[name=street2]").val(msg.street2);
                        jQuery("#credomaticMastercardPaymentForm input[name=cvv]").val(cvv_);
                        jQuery("#credomaticMastercardPaymentForm input[name=ccnumber]").val(number);
                        jQuery("#credomaticMastercardPaymentForm input[name=ccexp]").val(ccexp);
                        jQuery("#credomaticMastercardPaymentForm input[name=redirect]").val(msg.redirect);
                        if(jQuery("#credomaticPaymentForm input[name=redirect]").val() == ''){
                            setTimeout(function(){
                                jQuery('#credomaticPaymentForm').submit();
                            }, 500)
                        }
                        jQuery('#credomaticPaymentForm').submit();
                    }
                window.location.href = urlPaymentResponse;
                })
                .fail(function(msg){
                    window.location.href = urlPaymentResponse;
                })
                return false;
            },

            updateMenu: function () {
                let menu = $("#payment_methods_menu").find('ul');

                if (menu.length) {
                    let title_cont = $(".payment-method-title.credomaticmastercard");
                    let title = $(title_cont).find('label.label span').text();
                    let code_payment = $(title_cont).find('input').attr('id');

                    title_cont.hide();

                    $(menu).prepend(
                        '<li role="presentation" class="payment-group-item debitcard active">' +
                            '<a class="link_option_credomaticmastercard" style="padding-bottom: .5rem !important;" id="link-' + code_payment + '" data-code="' + code_payment + '">' + title + '</a>' +
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
