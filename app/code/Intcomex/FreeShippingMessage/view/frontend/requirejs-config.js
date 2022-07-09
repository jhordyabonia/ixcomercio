var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/minicart': {
                'Intcomex_FreeShippingMessage/js/mixin/view/minicart-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Intcomex_FreeShippingMessage/js/mixin/view/shipping-mixin': true
            },
            'Magento_Checkout/js/view/payment/list': {
                'Intcomex_FreeShippingMessage/js/mixin/view/payment/list-mixin': true
            }
        }
    }
};
