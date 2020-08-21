var config = {
    map: {
        '*': {
            addToCartDataLayer: 'MagePal_EnhancedEcommerce/js/add-to-cart-datalayer',
            checkOutDataLayer: 'MagePal_EnhancedEcommerce/js/checkout-datalayer',
            addToCartAjaxDataLayer: 'MagePal_EnhancedEcommerce/js/add-to-cart-ajax-datalayer',
            productClickDataLayer: 'MagePal_EnhancedEcommerce/js/product-click-datalayer',
            enhancedDataLayer: 'MagePal_EnhancedEcommerce/js/datalayer'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'MagePal_EnhancedEcommerce/js/mixin/shipping-mixin': true
            },
            'CyberSource_Address/js/view/cybersource-shipping': {
                'MagePal_EnhancedEcommerce/js/mixin/shipping-mixin': true
            },
            'Magento_Checkout/js/view/payment/default': {
                'MagePal_EnhancedEcommerce/js/mixin/payment/default-mixin': true
            },
            'Magento_Checkout/js/view/form/element/email':{
                'MagePal_EnhancedEcommerce/js/mixin/view/form/element/email-mixin': true
            }
        }
    }
};
