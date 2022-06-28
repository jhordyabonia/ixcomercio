var config = {
	map: {
      '*': {
        bootstrap: "js/bootstrap.bundle",
        owlCarouselJs: "js/owl.carousel.min",
        ownCarouselJs: "js/carousel",
        mainJs: "js/main.min"
		}
	},
    paths: {
        "bootstrap": "js/bootstrap.bundle",
        "owlCarouselJs": "js/owl.carousel.min",
        "ownCarouselJs": "js/carousel",
        "mainJs": "js/main.min"
    },
    shim: {
        'bootstrap': {
            'deps': ['jquery']
        },
        'owlCarouselJs': {
            'deps': ['jquery']
        },
        'ownCarouselJs': {
            'deps': ['jquery']
        },
        'mainJs': {
        	'deps': ['jquery']
        }
    }
};