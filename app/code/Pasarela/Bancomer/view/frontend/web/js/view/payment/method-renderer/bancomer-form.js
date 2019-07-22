/**
 * Pasarela_Bancomer Magento JS component
 *
 * @category    Bancomer
 * @package     Pasarela_Bancomer
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $, quote, customer) {
        'use strict';
        
        var customerData = null; 
        var total = window.checkoutConfig.payment.total;     
        
        return Component.extend({
            defaults: {
                template: 'Pasarela_Bancomer/payment/bancomer-form'
            },

            getCode: function() {
                return 'pasarela_bancomer';
            },

            isActive: function() {
                return true;
            },
            
            isLoggedIn: function() {
                console.log('isLoggedIn()', window.checkoutConfig.payment.is_logged_in);
                return window.checkoutConfig.payment.is_logged_in;
            },       
            /**
             * @override
             */
            getCustomerFullName: function() {             
                customerData = quote.billingAddress._latestValue;  
                return customerData.firstname+' '+customerData.lastname;                
            },
            validateAddress: function() {
                customerData = quote.billingAddress._latestValue;  
                if(typeof customerData.city === 'undefined' || customerData.city.length === 0) {
                  return false;
                }

                if(typeof customerData.countryId === 'undefined' || customerData.countryId.length === 0) {
                  return false;
                }

                if(typeof customerData.postcode === 'undefined' || customerData.postcode.length === 0) {
                  return false;
                }

                if(typeof customerData.street === 'undefined' || customerData.street[0].length === 0) {
                  return false;
                }                

                if(typeof customerData.region === 'undefined' || customerData.region.length === 0) {
                  return false;
                }
                
                var address = {
                    city: customerData.city,
                    country_code: customerData.countryId,
                    postal_code: customerData.postcode,
                    state: customerData.region,
                    line1: customerData.street[0],
                    line2: customerData.street[1]
                }

                return address;
            }
        });
    }
);
