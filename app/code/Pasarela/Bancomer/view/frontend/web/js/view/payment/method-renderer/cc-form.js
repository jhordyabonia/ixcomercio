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
                template: 'Pasarela_Bancomer/payment/openpay-form'
            },

            getCode: function() {
                return 'pasarela_bancomer';
            },

            isActive: function() {
                return true;
            },
            
            getMonthsInterestFree: function() {
                return window.checkoutConfig.payment.months_interest_free;                
            },
            
            creditCardOption: function() {
                console.log('#bancomer_cc', $('#bancomer_cc').val());                  
                if ($('#bancomer_cc').val() !== "new") {                                 
                    $('#save_cc').prop('checked', false);                
                    $('#save_cc').prop('disabled', true);                 
                    
                    $('#pasarela_bancomer_cc_number').val("");                                     
                    $("#pasarela_bancomer_expiration").val("").change();
                    $("#pasarela_bancomer_expiration_yr").val("").change();
                    $('#pasarela_bancomer_cc_cid').val("");                                                         
                    
                    $('#save_cc_fieldset').hide();                    
                    $('#payment_form_pasarela_bancomer').hide();
                } else {                    
                    $('#payment_form_pasarela_bancomer').show();
                    $('#save_cc_fieldset').show();
                    $('#save_cc').prop('disabled', false);
                }
            },
            
            showMonthsInterestFree: function() {
                var months = window.checkoutConfig.payment.months_interest_free;                         
                var total = window.checkoutConfig.payment.total;                
                total = parseInt(total);
                                
                return months.length > 1 ? true : false;                
            },
            
            canSaveCC: function() {
                return window.checkoutConfig.payment.can_save_cc === '1' ? true : false;                
            },
            
            isLoggedIn: function() {
                console.log('isLoggedIn()', window.checkoutConfig.payment.is_logged_in);
                return window.checkoutConfig.payment.is_logged_in;
            },            
            
            getCreditCardList: function() {
                console.log('getCreditCardList()', window.checkoutConfig.payment.cc_list);
                return window.checkoutConfig.payment.cc_list;
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
                
                if ($('#bancomer_cc').val() !== 'new') {
                    console.log('Pagar con token', $('#bancomer_cc').val());
                    this.messageContainer.clear();                                        
                    self.placeOrder();
                    return;
                }

                if ($form.validation() && $form.validation('isValid')) {
                    this.messageContainer.clear();                                        

                    var year_full = $('#pasarela_bancomer_expiration_yr').val();
                    var holder_name = this.getCustomerFullName();
                    var card = $('#pasarela_bancomer_cc_number').val();
                    var cvc = $('#pasarela_bancomer_cc_cid').val();
                    var year = year_full.toString().substring(2, 4);
                    var month = $('#pasarela_bancomer_expiration').val();

                    var data = {
                        holder_name: holder_name,
                        card_number: card.replace(/ /g, ''),
                        expiration_month: month || 0,
                        expiration_year: year,
                        cvv2: cvc
                    };

                    if(this.validateAddress() !== false){
                        data["address"] = this.validateAddress();
                    }

                    OpenPay.token.create(data, function(response) {
                            var token_id = response.data.id;
                            $("#bancomer_token").val(token_id);                            
                            
                            if (!response.data.card.points_card || !useCardPoints) {                                
                                console.log('NO useCardPoints');
                                self.placeOrder();
                                return;
                            } 
                            
                            var r = confirm("¿Desea usar los puntos de su tarjeta para realizar este pago?");
                            if (r === true) {
                                $('#use_card_points').val('true');                                                        
                            } else {
                                $('#use_card_points').val('false');                        
                            } 
                            self.placeOrder();
                            //$("#card-points-dialog").modal("show");                            
                        },
                        function(response) {
                            console.log("token error");
                            self.messageContainer.addErrorMessage({
                                message: response.data.description
                            });
                        }
                    );
                } else {
                    return $form.validation() && $form.validation('isValid');
                }
            },
            /**
             * @override
             */
            getData: function () {                
                return {
                    'method': "pasarela_bancomer",
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'bancomer_token': $("#bancomer_token").val(),
                        'device_session_id': $('#device_session_id').val(),
                        'interest_free': $('#interest_free').val(),
                        'use_card_points': $('#use_card_points').val(),
                        'save_cc': $("#save_cc").is(':checked') ? '1' : '0',
                        'bancomer_cc': $('#bancomer_cc').val()
                    }
                };
            },
            validate: function() {
                if ($('#bancomer_cc').val() !== 'new') {
                    console.log('validate', $('#bancomer_cc').val());
                    return true;
                }
                
                var $form = $('#' + this.getCode() + '-form');                
                return $form.validation() && $form.validation('isValid');
            },
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
