/**
 * Credomatic_MasterCard for Magento JS component
 *
 * @category    Credomatic_MasterCard
 * @package     Credomatic_MasterCard
 * @author      Intcomex
 * @copyright   Intcomex (https://www.intcomex.com/)
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'Intcomex_Credomatic_MasterCard/js/view/payment/cc-form',
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
                template: 'Intcomex_Credomatic_MasterCard/payment/stripe-form',
            	/*paymentPayload: {
                    nonce: null
                },
		creditCardInstallments: '',*/
	    },
	    
            getCode: function() {
                return 'credomatic_masterCard';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            afterPlaceOrder: function () { 
                
                var serviceUrl = url.build('credomatic_masterCard/custom/getorder');  
                var cuotas = $("#credomatic_mastercard_installments option:selected").val();
                var year = $("#credomatic_mastercard_expiration_yr option:selected").val();
                var month = $("#credomatic_mastercard_expiration option:selected").val();
                $.post(serviceUrl,{cart_id:quote.getQuoteId(),cuotas:cuotas,year:year,month:month})
                .done(function(msg){
                   var data = JSON.parse(JSON.stringify(msg));
                
                    $("#credomatic_mastercard_key_id").val(data.key_id);
                    $("#credomatic_mastercard_hash").val(data.hash);
                    $("#credomatic_mastercard_time").val(data.time);
                    $("#credomatic_mastercard_amount").val(data.amount);
                    $("#credomatic_mastercard_orderid").val(data.orderid);
                    $("#credomatic_mastercard_processor_id").val(data.processor_id);
                    $("#credomatic_mastercard_ccnumber").val($("#credomatic_mastercard_cc_number").val());
                    $("#credomatic_mastercard_ccexp").val(data.ccexp);
                    $("#credomatic_mastercard_cvv").val($("#credomatic_mastercard_cc_cid").val());
                    $("#credomatic_mastercard_redirect").val(url.build('credomatic_masterCard/custom/paymentresponse'));
                    setTimeout(function(){ 
                        $("#formCredomaticMasterCard").submit();
                    }, 2000);                
                })
                .fail(function(msg){

                })             

                return false;
            }
	                
            
        });
    }
);
