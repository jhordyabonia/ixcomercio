/**
 * PagaloVisa for Magento JS component
 *
 * @category    PagaloVisa
 * @package     PagaloVisa
 * @author      PagaloVisa
 * @copyright   PagaloVisa (https://www.pagalo.com/)
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_PagaloVisa/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'mage/translate'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_PagaloVisa/payment/stripe-form',
            	/*paymentPayload: {
                    nonce: null
                },
		creditCardInstallments: '',*/
	    },
	    
            getCode: function() {
                return 'pagalovisa';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                setTimeout(function(){
                    $("#pagalovisa_cc_number-error").text($.mage.__('Ingrese un Número de Tarjeta VISA válido'));
                    $("#pagalovisa_cc_number-error").show();
               }, 10);
                return $form.validation() && $form.validation('isValid');
            },
	                
            
        });
    }
);
