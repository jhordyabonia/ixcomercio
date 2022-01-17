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
    function (Component, $,valid,url) {
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
