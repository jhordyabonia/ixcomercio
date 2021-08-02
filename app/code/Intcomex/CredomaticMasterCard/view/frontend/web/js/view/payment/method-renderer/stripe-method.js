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
        'Intcomex_CredomaticMasterCard/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'mage/url',
        'mage/translate'

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
                setTimeout(function(){
                    $("#credomaticmastercard_cc_number-error").text($.mage.__('Ingrese un Número de Tarjeta MasterCard válido'));
                    $("#credomaticmastercard_cc_number-error").show();
               }, 10);
                return $form.validation() && $form.validation('isValid');
            },

            afterPlaceOrder: function () { 
                
                var serviceUrl = url.build('credomaticmastercard/custom/getorder');  
                var cuotas = $("#credomaticmastercard_installments option:selected").val();
                var year = $("#credomaticmastercard_expiration_yr option:selected").val();
                var month = $("#credomaticmastercard_expiration option:selected").val();
                $.post(serviceUrl,{cart_id:quote.getQuoteId(),cuotas:cuotas,year:year,month:month})
                .done(function(msg){
                   var data = JSON.parse(JSON.stringify(msg));
                
                    $("#credomaticmastercard_key_id").val(data.key_id);
                    $("#credomaticmastercard_hash").val(data.hash);
                    $("#credomaticmastercard_time").val(data.time);
                    $("#credomaticmastercard_amount").val(data.amount);
                    $("#credomaticmastercard_orderid").val(data.orderid);
                    $("#credomaticmastercard_processor_id").val(data.processor_id);
                    $("#credomaticmastercard_ccnumber").val($("#credomaticmastercard_cc_number").val());
                    $("#credomaticmastercard_ccexp").val(data.ccexp);
                    $("#credomaticmastercard_cvv").val($("#credomaticmastercard_cc_cid").val());
                    $("#credomaticmastercard_redirect").val(url.build('credomaticmastercard/custom/paymentresponse'));
                    setTimeout(function(){ 
                        $("#formCredomaticMasterCad").submit();
                    }, 2000);                
                })
                .fail(function(msg){

                })             

                return false;
            }
	                
            
        });
    }
);
