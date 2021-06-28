var config = {
	map: {
      '*': {
        bootstrap: "js/bootstrap.bundle",
        owlCarouselJs: "js/owl.carousel.min",
        mainJs: "js/main.min",
        'MercadoPago_Core/js/view/method-renderer/custom_method':
        'Magento_Checkout/js/view/method-renderer/mercado-pago/custom_method',
		}
	},
    paths: {
        "bootstrap": "js/bootstrap.bundle",
        "owlCarouselJs": "js/owl.carousel.min",
        "mainJs": "js/main.min"
    },
    shim: {
        'bootstrap': {
            'deps': ['jquery']
        },
        'owlCarouselJs': {
            'deps': ['jquery']
        },
        'mainJs': {
        	'deps': ['jquery']
        }
    }
};