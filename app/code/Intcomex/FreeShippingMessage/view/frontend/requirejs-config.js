var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/minicart': {
                'Intcomex_FreeShippingMessage/js/mixin/view/minicartv2-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Intcomex_FreeShippingMessage/js/mixin/view/shippingv2-mixin': true
            },
            'Magento_Checkout/js/view/payment/list': {
                'Intcomex_FreeShippingMessage/js/mixin/view/payment/listv2-mixin': true
            }
        }
    }
};
