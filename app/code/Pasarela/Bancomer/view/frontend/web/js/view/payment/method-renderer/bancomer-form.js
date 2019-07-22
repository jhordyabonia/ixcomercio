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

        //console.log(window.checkoutConfig.customerData);
        //console.log(customer.customerData);
        //console.log(quote.billingAddress._latestValue);
        var customerData = null; 
        var total = window.checkoutConfig.payment.total;        
        
        $(document).on("change", "#interest_free", function() {        
            var monthly_payment = 0;
            var months = parseInt($(this).val());     

            if (months > 1) {
                $("#total-monthly-payment").css("display", "inline");
            } else {
                $("#total-monthly-payment").css("display", "none");
            }

            monthly_payment = total/months;
            monthly_payment = monthly_payment.toFixed(2);            
            
            $("#monthly-payment").text(monthly_payment);
        });            
        
        //$("body").append('<div class="modal fade" role="dialog" id="card-points-dialog"> <div class="modal-dialog modal-sm"> <div class="modal-content"> <div class="modal-header"> <h4 class="modal-title">Pagar con Puntos</h4> </div> <div class="modal-body"> <p>¿Desea usar los puntos de su tarjeta para realizar este pago?</p> </div> <div class="modal-footer"> <button type="button" class="btn btn-success" data-dismiss="modal" id="points-yes-button">Si</button> <button type="button" class="btn btn-default" data-dismiss="modal" id="points-no-button">No</button> </div> </div> </div></div>');
        
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
             * Prepare and process payment information
             */
            preparePayment: function () {
                var self = this;
                var $form = $('#' + this.getCode() + '-form');
                
                var isSandbox = window.checkoutConfig.payment.bancomer_credentials.is_sandbox === "0" ? false : true;
                var useCardPoints = window.checkoutConfig.payment.use_card_points === "0" ? false : true;
                OpenPay.setId(window.checkoutConfig.payment.bancomer_credentials.merchant_id);
                OpenPay.setApiKey(window.checkoutConfig.payment.bancomer_credentials.public_key);
                OpenPay.setSandboxMode(isSandbox);                    

                //antifraudes
                OpenPay.deviceData.setup(this.getCode() + '-form', "device_session_id");
                
                console.log('#bancomer_cc', $('#bancomer_cc').val());
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
