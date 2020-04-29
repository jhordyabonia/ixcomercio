var config = {
	map: {
      '*': {
        mainJs: 'js/main.min',
		}
	},
    paths: {
    	"bootstrap": "js/bootstrap.bundle",
      "mainJs": "js/main.min"
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