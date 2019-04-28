var config = {
    paths: {
		'bootstrap':'Magento_Theme/js/bootstrap.bundle',
		'mainjs': ['js/main.min.js']
    } ,
    shim: {
        'bootstrap': {
            'deps': ['jquery']
        },
        'customjs': {
        	'deps': ['jquery']  
        }
    }
};