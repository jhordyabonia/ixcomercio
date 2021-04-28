var config = {

    'config': {
        'mixins': {
            'MercadoPago_Core/js/view/method-renderer/custom-method': {
                'Intcomex_MercadopagoRewrites/js/view/method-renderer/custom-method': true
            },
            'MercadoPago_Core/js/view/method-renderer/custom-method-ticket': {
                'Intcomex_MercadopagoRewrites/js/view/method-renderer/custom-method-ticket': true
            }
        }
    },
    map: {
        '*': {
            'MercadoPago_Core/template/payment/custom_method.html':
                'Intcomex_MercadopagoRewrites/template/payment/custom_method.html',
            'MercadoPago_Core/template/payment/custom_ticket.html':
                'Intcomex_MercadopagoRewrites/template/payment/custom_ticket.html'

        }
    }
};