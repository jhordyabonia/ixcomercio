var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/minicart': {
                'Intcomex_FreeShippingMessagev2/js/mixin/view/minicartv3-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Intcomex_FreeShippingMessagev2/js/mixin/view/shippingv3-mixin': true
            },
            'Magento_Checkout/js/view/payment/list': {
                'Intcomex_FreeShippingMessagev2/js/mixin/view/payment/listv3-mixin': true
            }
        }
    }
};
