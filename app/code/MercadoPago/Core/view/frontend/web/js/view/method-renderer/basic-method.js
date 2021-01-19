define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'MercadoPago_Core/js/model/set-analytics-information',
        'mage/translate',
        'jquery',
        'MPv1Ticket'
    ],
    function (Component, quote, setAnalyticsInformation, $t, $) {
        'use strict';

        let configPayment = window.checkoutConfig.payment.mercadopago_basic;

        return Component.extend({
            defaults: {
                template: 'MercadoPago_Core/payment/basic_method',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,

            initializeMethod: function () {

                var self = this;
                var mercadopago_site_id = window.checkoutConfig.payment[this.getCode()]['country']
                var payer_email = "";

                if (typeof quote == 'object' && typeof quote.guestEmail == 'string') {
                    payer_email = quote.guestEmail
                }

                if (mercadopago_site_id == 'MLB') {
                    this.setBillingAddress();
                }
            },

            setBillingAddress: function (t) {
                if (typeof quote == 'object' && typeof quote.billingAddress == 'function') {
                    var billingAddress = quote.billingAddress();
                    var address = "";
                    var number = "";

                    if ("street" in billingAddress) {
                        if (billingAddress.street.length > 0) {
                            address = billingAddress.street[0]
                        }
                        if (billingAddress.street.length > 1) {
                            number = billingAddress.street[1]
                        }
                    }

                    document.querySelector(MPv1Ticket.selectors.firstName).value = "firstname" in billingAddress ? billingAddress.firstname : '';
                    document.querySelector(MPv1Ticket.selectors.lastName).value = "lastname" in billingAddress ? billingAddress.lastname : '';
                    document.querySelector(MPv1Ticket.selectors.address).value = address;
                    document.querySelector(MPv1Ticket.selectors.number).value = number;
                    document.querySelector(MPv1Ticket.selectors.city).value = "city" in billingAddress ? billingAddress.city : '';
                    document.querySelector(MPv1Ticket.selectors.state).value = "regionCode" in billingAddress ? billingAddress.regionCode : '';
                    document.querySelector(MPv1Ticket.selectors.zipcode).value = "postcode" in billingAddress ? billingAddress.postcode : '';
                }
            },

            initObservable: function () {
                this._super().observe('paymentReady');

                return this;
            },
            isPaymentReady: function () {
                return this.paymentReady();
            },

            /**
             *
             */
            afterPlaceOrder: function () {
                window.location = this.getActionUrl();
            },

            /**
             * Places order in pending payment status.
             */
            placePendingPaymentOrder: function () {
                this.placeOrder();
            },
            initialize: function () {
                this._super();
                setAnalyticsInformation.beforePlaceOrder(this.getCode());
            },


            /**
             * @returns {string}
             */
            getCode: function () {
                return 'mercadopago_basic';
            },

            /**
             * @returns {*}
             */
            getLogoUrl: function () {
                if (configPayment !== undefined) {
                    return configPayment['logoUrl'];
                }
                return '';
            },

            /**
             *
             * @returns {boolean}
             */
            existBanner: function () {
                if (configPayment !== undefined) {
                    if (configPayment['bannerUrl'] != null) {
                        return true;
                    }
                }
                return false;
            },

            /**
             *
             * @returns {*}
             */
            getBannerUrl: function () {
                if (configPayment !== undefined) {
                    return configPayment['bannerUrl'];
                }
                return '';
            },

            /**
             *
             * @returns {*}
             */
            getActionUrl: function () {
                if (configPayment !== undefined) {
                    return configPayment['actionUrl'];
                }
                return '';
            },


            /**
             *
             * Basic Checkout
             */

            getRedirectImage: function () {
                return configPayment['redirect_image'];
            },

            getInfoBanner: function ($pm) {
                if (configPayment !== undefined) {
                    return configPayment['banner_info'][$pm];
                }
                return 0;
            },

            getInfoBannerInstallments: function () {
                if (configPayment !== undefined) {
                    return configPayment['banner_info']['installments'];
                }
                return 0;
            },

            getInfoBannerPaymentMethods: function ($pmFilter) {
                var listPm = []

                if (configPayment !== undefined) {
                    var paymetMethods = configPayment['banner_info']['checkout_methods'];
                    if (paymetMethods) {

                        for (var x = 0; x < paymetMethods.length; x++) {
                            var pmSelected = paymetMethods[x];
                            var insertList = false;

                            if ($pmFilter == 'credit') {
                                if (pmSelected.payment_type_id == 'credit_card') {
                                    insertList = true
                                }
                            } else if ($pmFilter == 'debit') {
                                if (pmSelected.payment_type_id == 'debit_card' || pmSelected.payment_type_id == 'prepaid_card') {
                                    insertList = true
                                }
                            } else {
                                if (pmSelected.payment_type_id != 'credit_card' && pmSelected.payment_type_id != 'debit_card' && pmSelected.payment_type_id != 'prepaid_card') {
                                    insertList = true
                                }
                            }

                            if (insertList) {
                                listPm.push({
                                    src: pmSelected.secure_thumbnail,
                                    name: pmSelected.name
                                });
                            }
                        }
                    }
                    return listPm;
                }
            },

            getCountryId: function () {
                return configPayment['country'];
            },

            /**
             * @override
             */
            getData: function () {

                var dataObj = {
                    'method': this.item.method,
                    'additional_data': {
                        'method': this.getCode(),
                        'site_id': this.getCountryId(),
                        'payment_method_ticket': this.getPaymentSelected(),
                        'coupon_code': document.querySelector(MPv1Ticket.selectors.couponCode).value
                    }
                };

                if (this.getCountryId() == 'MLB') {

                    //febraban rules
                    dataObj.additional_data.firstName = document.querySelector(MPv1Ticket.selectors.firstName).value
                    dataObj.additional_data.lastName = document.querySelector(MPv1Ticket.selectors.lastName).value
                    dataObj.additional_data.docType = MPv1Ticket.getDocTypeSelected();
                    dataObj.additional_data.docNumber = document.querySelector(MPv1Ticket.selectors.docNumber).value
                    dataObj.additional_data.address = document.querySelector(MPv1Ticket.selectors.address).value
                    dataObj.additional_data.addressNumber = document.querySelector(MPv1Ticket.selectors.number).value
                    dataObj.additional_data.addressCity = document.querySelector(MPv1Ticket.selectors.city).value
                    dataObj.additional_data.addressState = document.querySelector(MPv1Ticket.selectors.state).value
                    dataObj.additional_data.addressZipcode = document.querySelector(MPv1Ticket.selectors.zipcode).value

                }

                // return false;
                return dataObj;
            },
        });
    }
);
