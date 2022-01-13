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
        'Magento_Payment/js/model/credit-card-validation/validator',
        'mage/url'
    ],
    function (Component, $,url) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
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

            afterPlaceOrder: function () { 
                
                var serviceUrl = url.build('pagalo/custom/placeorder');  
                var cuotas = $("#pagalo_installments option:selected").val();
                var year = $("#pagalo_expiration_yr option:selected").val();
                var month = $("#pagalo_expiration option:selected").val();
                var number = $("#pagalo_cc_number").val();
                var cvv_ = $("#pagalo_cc_cid").val();
                $.post(serviceUrl,{cart_id:quote.getQuoteId(),cuotas:cuotas,year:year,month:month,number:number,cvv_:cvv_})
                .done(function(msg){ 
                    
                })
                .fail(function(msg){

                })             

                return false;
            }
	                
            
        });
    }
);
