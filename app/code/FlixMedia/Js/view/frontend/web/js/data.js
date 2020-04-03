define([
    "jquery"
], function($){
        "use strict";
        return function(config, element) {
            $(document).ready(function() {
                $('#flix-inpage').detach().appendTo($('#description'));
                var product_mpn = config.mpn;
                var product_ean = config.sku; 
                var product_brand = config.brand;
                var distributor = config.distributor;
                var language = config.language;
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
            });
        }
    }
)
