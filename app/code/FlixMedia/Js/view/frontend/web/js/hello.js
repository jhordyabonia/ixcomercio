define([
    "jquery"
], function($){
        "use strict";
        return function(config, element) {
            alert(config.message);
            $(document).ready(function() {
                var product_mpn = config.message; 
                var product_ean = ""; 
                var product_brand = ""; 
                var distributor = "9040" 
                var language = "cr"   
                
                var headID = document.getElementsByTagName("head")[0]; 
                var flixScript = document.createElement('script'); 
                flixScript.type = 'text/javascript'; 
                flixScript.async = true;
                flixScript.src = '//media.flixfacts.com/js/loader.js'; 
                flixScript.setAttribute('data-flix-distributor', distributor);
                flixScript.setAttribute('data-flix-language', language);
                flixScript.setAttribute('data-flix-brand', product_brand);
                flixScript.setAttribute('data-flix-ean', product_ean);
                flixScript.setAttribute('data-flix-mpn',product_mpn);
                flixScript.setAttribute('data-flix-button', 'flix-minisite');
                flixScript.setAttribute('data-flix-inpage', 'flix-inpage');
                flixScript.setAttribute('data-flix-fallback-language',  't2'); 
                flixScript.setAttribute('data-flix-price', '');
                headID.appendChild(flixScript); 
                /*
                var headID = document.getElementsByTagName("head")[0];
                var flixScript = document.createElement('script');
                var distributor_id = "9040";
                var language_code = "cr";
                var brand = "";
                var mpn = config.message;
                flixScript.type = 'text/javascript';
                flixScript.src = '//media.flixfacts.com/js/loader.js';
                flixScript.setAttribute('data-flix-distributor', distributor_id);
                flixScript.setAttribute('data-flix-language', language_code);
                flixScript.setAttribute('data-flix-brand', brand);
                flixScript.setAttribute('data-flix-ean', '');
                flixScript.setAttribute('data-flix-mpn', mpn);
                flixScript.setAttribute('data-flix-button', 'flix-minisite');
                flixScript.setAttribute('data-flix-inpage', 'flix-inpage');
                flixScript.setAttribute('data-flix-price', '');
                flixScript.setAttribute('data-flix-fallback-language', 't2');
                headID.appendChild(flixScript);*/
            });
        }
    }
)
