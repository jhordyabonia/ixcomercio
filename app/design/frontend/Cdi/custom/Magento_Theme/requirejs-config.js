var config = {
	map: {
      '*': {
        mainJs: 'js/main.min',
        zoomJs: 'lib/zoom/jquery.zoom.min'
		}
	},
    paths: {
    	"bootstrap": "Magento_Theme/js/bootstrap.bundle",
      "mainJs": "Magento_Theme/js/main.min",
      "zoomJs": "Magento_Theme/lib/zoom/jquery.zoom.min"
    },
    shim: {
        'bootstrap': {
            'deps': ['jquery']
        },
        'mainJs': {
        	'deps': ['jquery']
        },
        'zoomJs': {
          'deps': ['jquery']
        }
    }
};