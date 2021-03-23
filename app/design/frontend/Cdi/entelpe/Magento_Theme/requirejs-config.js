var config = {
	map: {
      '*': {
        mainJs: 'js/main.min',
        zoomJs: 'js/jquery.zoom.min'
		}
	},
    paths: {
    	"bootstrap": "Magento_Theme/js/bootstrap.bundle",
      "zoomJs": "Magento_Theme/js/jquery.zoom.min",
      "mainJs": "Magento_Theme/js/main.min"
    },
    shim: {
        'bootstrap': {
            'deps': ['jquery']
        },
        'zoomJs': {
          'deps': ['jquery']
        },
        'mainJs': {
        	'deps': ['jquery']
        }
    }
};