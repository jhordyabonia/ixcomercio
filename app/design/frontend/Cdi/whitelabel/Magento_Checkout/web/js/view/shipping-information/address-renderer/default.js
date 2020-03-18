/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-information/address-renderer/default'
        },

        /**
         * @param {*} countryId
         * @return {String}
         */
        getCountryName: function (countryId) {
            return '4';
            //return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },

        /**
         * @param {*} text
         * @return {String}
         */
        getCustomText: function (text) {
            return 'cuadtro' + text;
            //return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        }
    });
});
