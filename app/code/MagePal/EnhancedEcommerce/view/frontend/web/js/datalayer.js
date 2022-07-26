/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

define([
    'underscore',
    'jquery'
], function (_, $) {
    'use strict';

    return function (config) {
        window[config.dataLayer] = window[config.dataLayer] || [];
        var storedData = {};

        if (_.isArray(config.data)) {
            try {
                var storage = $.initNamespaceStorage('magepal-enhanced-ecommerce').localStorage;
                //{pid: productId, sku: product.id, list: list.list_type, position: product.position}
                storedData = storage.get('product-click');
            } catch (e) {
                storedData = {};
            }

            _.each(config.data, function (data) {
                if (_.has(data, 'event') && data.event === 'productDetail') {
                    if (_.isObject(storedData) && _.has(storedData, 'sku') && _.has(storedData, 'list')) {
                        if (_.contains(_.pluck(data.ecommerce.detail.products,"id"), storedData.sku)) {
                            data.ecommerce.actionField = {list: storedData.list};
                        }
                    }

                    if (_.isObject(storedData) && _.has(storedData, 'position')) {
                        data.list_position = storedData.position;
                    }
                }

                window[config.dataLayer].push(data);
            });
        }
    }

});
