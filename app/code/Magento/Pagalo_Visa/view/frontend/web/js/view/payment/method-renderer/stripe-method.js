/**
 * Pagalo_Visa for Magento JS component
 *
 * @category    Pagalo_Visa
 * @package     Pagalo_Visa
 * @author      Pagalo_Visa
 * @copyright   Pagalo_Visa (https://www.pagalo.com/)
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Pagalo_Visa/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Pagalo_Visa/payment/stripe-form',
            	/*paymentPayload: {
                    nonce: null
                },
		creditCardInstallments: '',*/
	    },
	    
            getCode: function() {
                return 'pagalo_visa';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },
	                
            
        });
    }
);
