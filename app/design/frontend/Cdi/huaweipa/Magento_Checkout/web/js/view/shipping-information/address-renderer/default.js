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
    var lastLabel = '';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-information/address-renderer/default'
        },

        /**
         * @param {*} countryId
         * @return {String}
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
        }
    });
});
