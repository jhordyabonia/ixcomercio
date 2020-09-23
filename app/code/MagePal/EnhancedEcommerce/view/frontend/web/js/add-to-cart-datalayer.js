/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

define([
    'jquery'
], function ($) {

    return function (config) {
        var dataLayer = window[config.dataLayerName];

        $(document).on('ajaxComplete', function (event, xhr, settings) {

            if (settings.url.match(/\/checkout\/cart\/add/i) || settings.url.match(/\/amasty_cart\/cart\/add/i)) {
                if (_.isObject(xhr.responseJSON)
                    && !_.has(xhr.responseJSON, 'backUrl')
                    && !_.isEmpty(_.pick(xhr.responseJSON, ['enhancedecommerce']))
                ) {
                    var storedData = {};

                    try {
                        var storage = $.initNamespaceStorage('magepal-enhanced-ecommerce').localStorage;
                        //{pid: productId, sku: product.id, list: list.list_type, position: product.position}
                        storedData = storage.get('product-click');
                    } catch (e) {
                        storedData = {};
                    }

                    var enhancedecommerce = xhr.responseJSON['enhancedecommerce'];

                    _.each(enhancedecommerce, function (data) {
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

                    $("body").trigger("mpItemAddToCart", [enhancedecommerce, dataLayer]);
                    dataLayer.push({
                        'event': 'addToCart',
                        'ecommerce': {
                            'add': {
                                'products': enhancedecommerce
                            }
                        },
                        'cart': {
                            'add': {
                                'products': enhancedecommerce
                            }
                        }
                    });
                }
            }
        });
    }
});
