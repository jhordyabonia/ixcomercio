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
                
                var serviceUrl = url.build('credomaticvisa/custom/getorder');  
                var cuotas = $("#credomaticvisa_installments option:selected").val();
                var year = $("#credomaticvisa_expiration_yr option:selected").val();
                var month = $("#credomaticvisa_expiration option:selected").val();
                $.post(serviceUrl,{cart_id:quote.getQuoteId(),cuotas:cuotas,year:year,month:month})
                .done(function(msg){
                   var data = JSON.parse(JSON.stringify(msg));
                
                    $("#credomaticvisa_key_id").val(data.key_id);
                    $("#credomaticvisa_hash").val(data.hash);
                    $("#credomaticvisa_time").val(data.time);
                    $("#credomaticvisa_amount").val(data.amount);
                    $("#credomaticvisa_orderid").val(data.orderid);
                    $("#credomaticvisa_processor_id").val(data.processor_id);
                    $("#credomaticvisa_ccnumber").val($("#credomaticvisa_cc_number").val());
                    $("#credomaticvisa_ccexp").val(data.ccexp);
                    $("#credomaticvisa_cvv").val($("#credomaticvisa_cc_cid").val());
                    $("#credomaticvisa_redirect").val(url.build('credomaticvisa/custom/paymentresponse'));
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
