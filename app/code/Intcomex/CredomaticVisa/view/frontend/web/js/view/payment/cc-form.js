/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'underscore',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Payment/js/model/credit-card-validation/credit-card-data',
    'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
    'mage/translate',
    'https://s3.amazonaws.com/documentacionpagalo/archivos/cybs_devicefingerprint.js',
    'jquery',
    'mage/storage',
    'mage/url'
], function (_, Component, creditCardData, cardNumberValidator, $t,__, $, storage, url) {
    'use strict';

    return Component.extend({
        defaults: {
            creditCardType: '',
            creditCardExpYear: '',
            creditCardExpMonth: '',
            creditCardNumber: '',
            creditCardSsStartMonth: '',
            creditCardSsStartYear: '',
            creditCardSsIssue: '',
            creditCardVerificationNumber: '',
            selectedCardType: null,
            creditCardInstallments: '', 
            template: 'Intomex_Credomatic_Visa/payment/cc-form'
        },
        
        getconfigValueVisa: function () {
            var serviceUrl = url.build('credomatic_visa/custom/storeconfig');  
            storage.get(serviceUrl).done(
                function (response) {
                    if (response.success) {
                        var response = response.value.split(',');
                    
                        var newOptions = {
                            "Forma de pago": "",
                            "Al Contado": "1",
                        };

                        for (var i = 0; i < response.length; i++) {
                            response[i];
                            newOptions[response[i]+" Cuotas"] = response[i];
                        }
        
                        var $methods = $("#credomatic_visa_installments");
                        $methods.empty();
                        $.each(newOptions, function(key,value) {
                            $methods.append($("<option></option>").attr("value", value).text(key));
                        });
                        
                        return response.value

                    }
                }
            ).fail(
                function (response) {
                    return response.value
                }
            );
            
            return false;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe([
                    'creditCardType',
                    'creditCardExpYear',
                    'creditCardExpMonth',
                    'creditCardNumber',
                    'creditCardVerificationNumber',
                    'creditCardSsStartMonth',
                    'creditCardSsStartYear',
                    'creditCardSsIssue',
                    'selectedCardType',
                    'creditCardInstallments'
                ]);

            return this;
        },

        /**
         * Init component
         */
        initialize: function () {
            var self = this;

            this._super();

            //Set credit card number to credit card data object
            this.creditCardNumber.subscribe(function (value) {
                var result;

                self.selectedCardType(null);

                if (value === '' || value === null) {
                    return false;
                }
                result = cardNumberValidator(value);

                if (!result.isPotentiallyValid && !result.isValid) {
                    return false;
                }

                if (result.card !== null) {
                    self.selectedCardType(result.card.type);
                    creditCardData.creditCard = result.card;
                }

                if (result.isValid) {
                    creditCardData.creditCardNumber = value;
                    self.creditCardType(result.card.type);
                }
            });

            // Sets installments plan
            this.creditCardInstallments.subscribe(function( value ){
                creditCardData.installments = value;
            })

            //Set expiration year to credit card data object
            this.creditCardExpYear.subscribe(function (value) {
                creditCardData.expirationYear = value;
            });

            //Set expiration month to credit card data object
            this.creditCardExpMonth.subscribe(function (value) {
                creditCardData.expirationMonth = value;
            });

            //Set cvv code to credit card data object
            this.creditCardVerificationNumber.subscribe(function (value) {
                creditCardData.cvvCode = value;
            });
        },

        /**
         * Get code
         * @returns {String}
         */
         getCodeVisa: function () {
            return 'cc';
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {
	    var parent = this._super();

            var additionalData = {
                'cc_cid': this.creditCardVerificationNumber(),
                'cc_ss_start_month': this.creditCardSsStartMonth(),
                'cc_ss_start_year': this.creditCardSsStartYear(),
                'cc_ss_issue': this.creditCardSsIssue(),
                'cc_type': this.creditCardType(),
                'cc_exp_year': this.creditCardExpYear(),
                'cc_exp_month': this.creditCardExpMonth(),
                'cc_number': this.creditCardNumber(),
                'cc_installments': this.creditCardInstallments(),
                'cc_fingerprint': cybs_dfprofiler("visanetgt_jupiter","live")
            };

            return $.extend(true, parent, {
                'additional_data': additionalData
            });
        },

        /**
         * Get list of available credit card types
         * @returns {Object}
         */
        getCcAvailableTypesVisa: function () {
            return window.checkoutConfig.payment.ccform.availableTypes[this.getCodeVisa()];
        },

        /**
         * Get payment icons
         * @param {String} type
         * @returns {Boolean}
         */
        getIcons: function (type) {
            return window.checkoutConfig.payment.ccform.icons.hasOwnProperty(type) ?
                window.checkoutConfig.payment.ccform.icons[type]
                : false;
        },

        /**
         * Get list of months
         * @returns {Object}
         */
        getCcMonths: function () {
            return window.checkoutConfig.payment.ccform.months[this.getCodeVisa()];
        },

        /**
         * Get list of years
         * @returns {Object}
         */
        getCcYears: function () {
            return window.checkoutConfig.payment.ccform.years[this.getCodeVisa()];
        },

        /**
         * Check if current payment has verification
         * @returns {Boolean}
         */
        hasVerification: function () {
            return window.checkoutConfig.payment.ccform.hasVerification[this.getCodeVisa()];
        },

        /**
         * @deprecated
         * @returns {Boolean}
         */
        hasSsCardType: function () {
            return window.checkoutConfig.payment.ccform.hasSsCardType[this.getCodeVisa()];
        },

        /**
         * Get image url for CVV
         * @returns {String}
         */
        getCvvImageUrl: function () {
            return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCodeVisa()];
        },

        /**
         * Get image for CVV
         * @returns {String}
         */
        getCvvImageHtml: function () {
            return '<img src="' + this.getCvvImageUrl() +
                '" alt="' + $t('Card Verification Number Visual Reference') +
                '" title="' + $t('Card Verification Number Visual Reference') +
                '" />';
        },

        /**
         * @deprecated
         * @returns {Object}
         */
        getSsStartYears: function () {
            return window.checkoutConfig.payment.ccform.ssStartYears[this.getCodeVisa()];
        },

        /**
         * Get list of available credit card types values
         * @returns {Object}
         */
        getCcAvailableTypesValuesVisa: function () {
            return _.map(this.getCcAvailableTypesVisa(), function (value, key) {
                return {
                    'value': key,
                    'type': value
                };
            });
        },

        /**
         * Get list of available month values
         * @returns {Object}
         */
        getCcMonthsValues: function () {
            return _.map(this.getCcMonths(), function (value, key) {
                return {
                    'value': key,
                    'month': value
                };
            });
        },

	/**
         * Get list of available installment plans
         * @returns {Object}
         */
	getInstallmentsValues: function () {
	    return [
            {'value': 3, 'installment': 'TASA0 3'},
            {'value': 6, 'installment': 'TASA0 6'},
	    ];
        },


        /**
         * Get list of available year values
         * @returns {Object}
         */
        getCcYearsValues: function () {
            return _.map(this.getCcYears(), function (value, key) {
                return {
                    'value': key,
                    'year': value
                };
            });
        },

        /**
         * @deprecated
         * @returns {Object}
         */
        getSsStartYearsValues: function () {
            return _.map(this.getSsStartYears(), function (value, key) {
                return {
                    'value': key,
                    'year': value
                };
            });
        },

        /**
         * Is legend available to display
         * @returns {Boolean}
         */
        isShowLegend: function () {
            return false;
        },

        /**
         * Get available credit card type by code
         * @param {String} code
         * @returns {String}
         */
        getCcTypeTitleByCode: function (code) {
            var title = '',
                keyValue = 'value',
                keyType = 'type';

            _.each(this.getCcAvailableTypesValuesVisa(), function (value) {
                if (value[keyValue] === code) {
                    title = value[keyType];
                }
            });

            return title;
        },

        /**
         * Prepare credit card number to output
         * @param {String} number
         * @returns {String}
         */
        formatDisplayCcNumber: function (number) {
            return 'xxxx-' + number.substr(-4);
        },

        /**
         * Get credit card details
         * @returns {Array}
         */
        getInfo: function () {
            return [
                {
                    'name': 'Credit Card Type', value: this.getCcTypeTitleByCode(this.creditCardType())
                },
                {
                    'name': 'Credit Card Number', value: this.formatDisplayCcNumber(this.creditCardNumber())
                }
            ];
        }
    });
});

