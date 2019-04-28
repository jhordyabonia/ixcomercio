var config = {
    paths: {
    	"*": {
	    	"bootstrap": "Magento_Theme/js/bootstrap.bundle",
	      	"mainJs": "Magento_Theme/js/main.min"
	    }]
    } ,
    shim: {
        "bootstrap": {
            "deps": ["jquery"]
        },
        "mainJs": {
        	"deps": ['jquery']  
        }
    }
};