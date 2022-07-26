/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

define([
    'Magento_Customer/js/customer-data',
    'jquery',
    'underscore'
], function (customerData, $, _) {
    'use strict';

    function updateDataLayer(_gtmDataLayer, _dataObject, _forceUpdate)
    {
        if (_gtmDataLayer !== undefined && _forceUpdate) {
            if (_.isObject(_dataObject) && _.has(_dataObject, 'cartItems')) {
                var ecommerce;
                var storedData = {};

                try {
                    var storage = $.initNamespaceStorage('magepal-enhanced-ecommerce').localStorage;
                    //{pid: productId, sku: product.id, list: list.list_type, position: product.position}
                    storedData = storage.get('product-click');
                } catch (e) {
                    storedData = {};
                }

                var cartGenericLayer = {};
                _.each(_dataObject.cartItems, function (cartItem) {
                    if (_.has(cartItem, 'ecommerce')) {
                        ecommerce = cartItem.ecommerce;
                        if (_.has(cartItem.ecommerce, 'add')) {
                            var itemsAdded = ecommerce.add.products;

                            _.each(itemsAdded, function (data) {
                                if (_.has(data, 'parent_sku')) {
                                    if (_.isObject(storedData)
                                        && _.has(storedData, 'sku')
                                        &&  _.has(storedData, 'position')
                                        && data.parent_sku == storedData.sku
                                    ) {
                                        data.position = storedData.position;
                                    }
                                }
                            });

                            $("body").trigger("mpItemAddToCart", [itemsAdded, _gtmDataLayer]);
                            cartGenericLayer.add = {
                                'products': itemsAdded
                            };
                        }

                        if (_.has(cartItem.ecommerce, 'remove')) {
                            var itemsRemoved = ecommerce.remove.products;

                            _.each(itemsRemoved, function (data) {
                                if (_.has(data, 'parent_sku')) {
                                    if (_.isObject(storedData)
                                        && _.has(storedData, 'sku')
                                        &&  _.has(storedData, 'position')
                                        && data.parent_sku == storedData.sku
                                    ) {
                                        data.position = storedData.position;
                                    }
                                }
                            });

                            $("body").trigger("mpItemRemoveFromCart", [itemsRemoved, _gtmDataLayer]);
                            cartGenericLayer.remove = {
                                'products': itemsRemoved
                            };
                        }
                    }
                    cartItem.cart = cartGenericLayer;
                    cartItem.environment = 'production';
                    _gtmDataLayer.push(cartItem);
                });
            }
        }
    }

    return function (options) {
        var dataObject = customerData.get("magepal-eegtm-jsdatalayer");

        var gtmDataLayer = window[options.dataLayerName];

        dataObject.subscribe(function (_dataObject) {
            updateDataLayer(gtmDataLayer, _dataObject, true);
        }, this);
    }
});
