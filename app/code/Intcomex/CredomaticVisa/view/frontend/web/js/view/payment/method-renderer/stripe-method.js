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
        'mage/url',

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
	    
        getCodeVisa: function() {
                return 'credomatic_visa';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCodeVisa() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            afterPlaceOrder: function () { 
                
                var serviceUrl = url.build('credomatic_visa/custom/getorder');  
                var cuotas = $("#credomatic_visa_installments option:selected").val();
                var year = $("#credomatic_visa_expiration_yr option:selected").val();
                var month = $("#credomatic_visa_expiration option:selected").val();
                $.post(serviceUrl,{cart_id:quote.getQuoteId(),cuotas:cuotas,year:year,month:month})
                .done(function(msg){
                   var data = JSON.parse(JSON.stringify(msg));
                
                    $("#credomatic_visa_key_id").val(data.key_id);
                    $("#credomatic_visa_hash").val(data.hash);
                    $("#credomatic_visa_time").val(data.time);
                    $("#credomatic_visa_amount").val(data.amount);
                    $("#credomatic_visa_orderid").val(data.orderid);
                    $("#credomatic_visa_processor_id").val(data.processor_id);
                    $("#credomatic_visa_ccnumber").val($("#credomatic_visa_cc_number").val());
                    $("#credomatic_visa_ccexp").val(data.ccexp);
                    $("#credomatic_visa_cvv").val($("#credomatic_visa_cc_cid").val());
                    $("#credomatic_visa_redirect").val(url.build('credomatic_visa/custom/paymentresponse'));
                    setTimeout(function(){ 
                        $("#formCredomaticVisa").submit();
                    }, 2000);                
                })
                .fail(function(msg){

                })             

                return false;
            }
	                
            
        });
    }
);
