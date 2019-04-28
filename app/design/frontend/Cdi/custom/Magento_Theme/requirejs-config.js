var config = {
    paths: {
		'bootstrap':'Magento_Theme/js/bootstrap.bundle',
    } ,
    shim: {
        'bootstrap': {
            'deps': ['jquery']
        },
        'customjs': {
            'mainjs': ['js/main.min.js']
        }
    }
};