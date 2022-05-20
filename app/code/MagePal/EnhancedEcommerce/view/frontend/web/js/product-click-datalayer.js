define([
    'jquery',
    'underscore'
], function ($, _) {

    var productCollection = {};

    function getDataLayer(dataLayer)
    {
        var _dataLayer = [];

        _.each(dataLayer, function (item) {
            if (_.has(item, 'ecommerce')
                && _.has(item.ecommerce, 'impressions') && item.ecommerce.impressions.length
            ) {
                _dataLayer = _dataLayer.concat(item.ecommerce.impressions)
            }
        });

        return _dataLayer;
    }

    function getItemImpression(dataLayer)
    {
        var dataLayerArray = getDataLayer(dataLayer);
        var products = {};

        if (dataLayerArray.length) {
            _.each(dataLayerArray, function (productObj) {

                if (_.has(productObj, 'p_id')) {
                    var product = productObj;

                    if (_.has(productObj, 'category')) {
                        product['category'] =  productObj.category;
                    }

                    products[productObj.p_id] = product;
                }
            });
        }

        return products;
    }

    function productClick($element, list, dataLayer)
    {
        var $container = $element.closest(list.container_class);
        var $priceBox = $container.find("[data-product-id]");
        var productUrl = null;

        if ($container.find('a.product-item-link').length) {
            productUrl = $container.find('a.product-item-link').attr('href');
        } else if ($container.find('a.product-item-photo').length) {
            productUrl = $container.find('a.product-item-photo').attr('href');
        } else {
            productUrl = $element.attr('href');
        }

        if ($priceBox.length) {
            var productId = $priceBox.data('productId');

            if (productId && _.has(productCollection, productId)) {
                //if gtm take longer than 3 seconds
                var autoRedirectTimer = setTimeout(function () {
                    document.location = productUrl
                }, 3000);

                var product = productCollection[productId];

                try {
                    var storage = $.initNamespaceStorage('magepal-enhanced-ecommerce').localStorage;
                    storage.set('product-click', {
                        pid: productId,
                        sku: product.id,
                        list: list.list_type,
                        position: product.position
                    });
                } catch (e) {
                }

                $("body").trigger("mpProductClick", [product, dataLayer]);

                dataLayer.push({
                    environment: 'production',
                    'event': 'productClick',
                    'ecommerce': {
                        'click': {
                            'actionField': {
                                'list': list.list_type
                            },
                            'products': [product]
                        }
                    },
                    'eventCallback': function () {
                        clearTimeout(autoRedirectTimer);
                        document.location = productUrl
                    }
                });

                return false;
            }
        }

        return true;
    }

    return function (config) {

        var dataLayer = window[config.dataLayerName];

        _.each(config.productLists, function (list) {
            $(list.class_name).on('click', function () {
                return productClick($(this), list, dataLayer);
            });
        });

        productCollection = getItemImpression(dataLayer);

    }
});
