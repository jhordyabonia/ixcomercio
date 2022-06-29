var config = {
    map: {
      '*': {
        bootstrap: "js/bootstrap.bundle",
        owlCarouselJs: "js/owl.carousel.min",
        myscript: 'js/carousel',
        mainJs: "js/main.min"
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