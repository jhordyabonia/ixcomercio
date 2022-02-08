/**
 * PagaloMasterCard for Magento JS component
 *
 * @category    PagaloMasterCard
 * @package     PagaloMasterCard
 * @author      PagaloMasterCard
 * @copyright   PagaloMasterCard (https://www.pagalo.com/)
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_PagaloMasterCard/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'mage/url'
    ],
    function (Component, $,valid,url) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Magento_PagaloMasterCard/payment/stripe-form',
            	/*paymentPayload: {
                    nonce: null
                },
		creditCardInstallments: '',*/
	    },
	    
            getCode: function() {
                return 'pagalomastercard';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                setTimeout(function(){
                    $("#pagalomastercard_cc_number-error").text($.mage.__('Ingrese un Número de Tarjeta MasterCard válido'));
                    $("#pagalomastercard_cc_number-error").show();
               }, 10);
                return $form.validation() && $form.validation('isValid');
            },
            afterPlaceOrder: function () { 
                var serviceUrl = url.build('pagalo/custom/checkorder');  
                jQuery.post(serviceUrl)
                .done(function(msg){
                    console.log(msg);
                    setTimeout(function(){
                        window.location.href = url.build(msg.message.redirect);                    
                    }, 1500);
                })
                .fail(function(){

                })
            }
	                
            
        });
    }
);
