define([
    'jquery',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data',
],
    function (
        $,
        addressList,
        quote,
        selectBillingAddress,
        checkoutData,
        customerData
    ) {
        'use strict';

        var lastSelectedBillingAddress = null,
        countryData = customerData.get('directory-data'),
        lastLabel = '',
        addressOptions = addressList().filter(function (address) {
            return address.getType() === 'customer-address';
        });

        return function (Component) {
            return Component.extend({

                /**
                 * @return {Boolean}
                 */
                useShippingAddress: function () {

                    var payment_method = checkoutData.getSelectedPaymentMethod();
                    
                    if(payment_method.length){

                        var cdi_checkout_identification = jQuery('div[name="billingAddress' + payment_method + '.custom_attributes.identification"] .label span');
                        var cdi_checkout_name_label = jQuery('div[name="billingAddress' + payment_method + '.custom_attributes.firstname"] .label span');
                        var cdi_checkout_address_label = jQuery('.field.street.admin__control-fields .label span');
                        
                        if(window.checkoutConfig.cdi_checkout_name_label != ""){
                            cdi_checkout_name_label.text(window.checkoutConfig.cdi_checkout_name_label);
                        }

                        if(window.checkoutConfig.cdi_checkout_identification != "" ){
                            cdi_checkout_identification.text(window.checkoutConfig.cdi_checkout_identification);
                        }

                        if(window.checkoutConfig.cdi_checkout_address_label != ""){
                            cdi_checkout_address_label.text(window.checkoutConfig.cdi_checkout_address_label);
                        }
                    }

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
                }
            });
        };
    });