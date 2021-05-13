/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

 define([
    'ko',
    'underscore',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/action/set-billing-address',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'jquery',
    'mage/url'
],
function (
    ko,
    _,
    Component,
    customer,
    addressList,
    quote,
    createBillingAddress,
    selectBillingAddress,
    checkoutData,
    checkoutDataResolver,
    customerData,
    setBillingAddressAction,
    globalMessageList,
    $t,
    shippingRatesValidator,
    $,
    url
) {
    'use strict';

    var lastSelectedBillingAddress = null,
        countryData = customerData.get('directory-data'),
        lastLabel = '',
        addressOptions = addressList().filter(function (address) {
            return address.getType() === 'customer-address';
        });

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/billing-address',
            actionsTemplate: 'Magento_Checkout/billing-address/actions',
            formTemplate: 'Magento_Checkout/billing-address/form',
            detailsTemplate: 'Magento_Checkout/billing-address/details',
            links: {
                isAddressFormVisible: '${$.billingAddressListProvider}:isNewAddressSelected'
            }
        },
        currentBillingAddress: quote.billingAddress,
        customerHasAddresses: addressOptions.length > 0,

        /**
         * Init component
         */
        initialize: function () {
            this._super();
            quote.paymentMethod.subscribe(function () {
                checkoutDataResolver.resolveBillingAddress();
            }, this);
            shippingRatesValidator.initFields(this.get('name') + '.form-fields');
        },

        /**
         * @return {exports.initObservable}
         */
        initObservable: function () {
            this._super()
                .observe({
                    selectedAddress: null,
                    isAddressDetailsVisible: quote.billingAddress() != null,
                    isAddressFormVisible: !customer.isLoggedIn() || !addressOptions.length,
                    isAddressSameAsShipping: false,
                    isInvoiceSelected: false,
                    saveInAddressBook: 1
                });

            quote.billingAddress.subscribe(function (newAddress) {
                if (quote.isVirtual()) {
                    this.isAddressSameAsShipping(false);
                } else {
                    this.isAddressSameAsShipping(
                        newAddress != null &&
                        newAddress.getCacheKey() == quote.shippingAddress().getCacheKey() //eslint-disable-line eqeqeq
                    );
                }

                if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                    this.saveInAddressBook(newAddress.saveInAddressBook);
                } else {
                    this.saveInAddressBook(1);
                }
                this.isAddressDetailsVisible(true);
            }, this);

            return this;
        },

        canUseShippingAddress: ko.computed(function () {
            return !quote.isVirtual() && quote.shippingAddress() && quote.shippingAddress().canUseForBilling();
        }),

        canUseInvoice: ko.computed(function () {
            return window.enableInvoice
        }),

        invoiceLabel: ko.computed(function () {
            return window.invoiceLabel
        }),

        customAlert: ko.computed(function () {
            findElement();

            document.addEventListener("click", function(){
                findElement();
            });
            function findElement(){
                  (function theLoop (i) {
                      setTimeout(function () {
                          if(jQuery("#checkout-shipping-method-load").length>0&&window.customAlert!=''){
                              if(jQuery(".custom_alert").length==0){
                                  jQuery("#checkout-shipping-method-load").after('<div class="custom_alert" style="color:red" ><img class="icon"  src="'+window.mediaUrl+'/icono_'+window.currentWebsiteCode+'.png" >'+window.customAlert+'</div>');
                              }
                              return false;
                          }
                          if (--i) {          // If i > 0, keep going
                          theLoop(i);       // Call the loop again, and pass it the current value of i
                          }
                      }, 1000);
                  })(40); 
              }
          
        }),

        /**
         * @param {Object} address
         * @return {*}
         */
        addressOptionsText: function (address) {
            return address.getAddressInline();
        },

        /**
         * @return {Boolean}
         */
        useShippingAddress: function () {
            if (this.isAddressSameAsShipping()) {
                selectBillingAddress(quote.shippingAddress());

                this.updateAddresses();
                this.isAddressDetailsVisible(true);
            } else {
                lastSelectedBillingAddress = quote.billingAddress();
                quote.billingAddress(null);
                this.isAddressDetailsVisible(false);
            }
            checkoutData.setSelectedBillingAddress(null);

            return true;
        },

        useInvoice: function () {

            //default value lastname
            $('input[name="lastname"]').val("N/A");
            
            var useinvoice = '';
            if (this.isInvoiceSelected()) {
                this.isInvoiceSelected(true);
                useinvoice = 'Yes';
            }else{
                this.isInvoiceSelected(false);
                useinvoice = 'No';
            }
            console.log('seting usenvoice');
                    var serviceUrl = url.build('cdiroude/index/setpaymentinfo');
                   jQuery.post(serviceUrl,{'useinvoice':useinvoice})
                    .done(function(msg){
                        console.log(msg);
                    })
                    .fail(function(msg){
                        console.log(msg);
                    });           
            return true;
        },

        /**
         * Update address action
         */
        updateAddress: function () {
            var addressData, newBillingAddress;

            if (this.selectedAddress() && !this.isAddressFormVisible()) {
                selectBillingAddress(this.selectedAddress());
                checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
            } else {
                this.source.set('params.invalid', false);
                this.source.trigger(this.dataScopePrefix + '.data.validate');

                if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                    this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                }

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get(this.dataScopePrefix);

                    if (customer.isLoggedIn() && !this.customerHasAddresses) { //eslint-disable-line max-depth
                        this.saveInAddressBook(1);
                    }
                    addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                    addressData['lastname'] = ".";
                    newBillingAddress = createBillingAddress(addressData);

                    // New address must be selected as a billing address
                    selectBillingAddress(newBillingAddress);
                    checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                    checkoutData.setNewCustomerBillingAddress(addressData);
                }
            }
            this.updateAddresses();
        },

        /**
         * Edit address action
         */
        editAddress: function () {
            lastSelectedBillingAddress = quote.billingAddress();
            quote.billingAddress(null);
            this.isAddressDetailsVisible(false);
        },

        /**
         * Cancel address edit action
         */
        cancelAddressEdit: function () {
            this.restoreBillingAddress();

            if (quote.billingAddress()) {
                // restore 'Same As Shipping' checkbox state
                this.isAddressSameAsShipping(
                    quote.billingAddress() != null &&
                        quote.billingAddress().getCacheKey() == quote.shippingAddress().getCacheKey() && //eslint-disable-line
                        !quote.isVirtual()
                );
                this.isAddressDetailsVisible(true);
            }
        },

        /**
         * Manage cancel button visibility
         */
        canUseCancelBillingAddress: ko.computed(function () {
            return quote.billingAddress() || lastSelectedBillingAddress;
        }),

        /**
         * Restore billing address
         */
        restoreBillingAddress: function () {
            if (lastSelectedBillingAddress != null) {
                selectBillingAddress(lastSelectedBillingAddress);
            }
        },

        /**
         * @param {Number} countryId
         * @return {*}
         */
        getCountryName: function (countryId) {
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },

        /**
         * @param {*} text
         * @return {String}
         */
        customLabelVisible: function (text) {
            if(text == 'zone_id' || text == 'identification'){
                lastLabel = text;
                return false;
            }
            return true;
        },

        /**
         * @param {*} text
         * @return {String}
         */
        getCustomText: function (text) {
            if(lastLabel == 'zone_id'){
                //
            }
            return text; 
        },

        /**
         * Trigger action to update shipping and billing addresses
         */
        updateAddresses: function () {
            if (window.checkoutConfig.reloadOnBillingAddress ||
                !window.checkoutConfig.displayBillingOnPaymentMethod
            ) {
                setBillingAddressAction(globalMessageList);
            }
        },

        /**
         * Get code
         * @param {Object} parent
         * @returns {String}
         */
        getCode: function (parent) {
            return _.isFunction(parent.getCode) ? parent.getCode() : 'shared';
        }
     
    });
});
