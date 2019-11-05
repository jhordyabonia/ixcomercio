var config = {
	map: {
      '*': {
        mainJs: 'js/main.min',
		}
	},
    paths: {
    	"bootstrap": "js/bootstrap.bundle",
      "mainJs": "Magento_Theme/js/main.min"
    },
    shim: {
        'bootstrap': {
            'deps': ['jquery']
        },
        'mainJs': {
        	'deps': ['jquery']
        }
    }
};