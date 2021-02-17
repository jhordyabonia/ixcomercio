var config = {
	map: {
      '*': {
        bootstrap: "js/bootstrap.bundle",
        owlCarouselJs: "js/owl.carousel.min",
        mainJs: "js/main.min",
        "Magento_SalesRule/js/action/set-coupon-code": "Intcomex_TradeIn/js/action/set-coupon-code",
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