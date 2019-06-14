require(['jquery', 'zoomJs', 'mainJs', 'domReady!'], function($) {
    
	jQuery('#scroll-to-top').click(function(){
		jQuery("html, body").animate({scrollTop: 0}, 600, "easeOutCubic");
		return false;
	});


	$(document).ajaxComplete(function() {
	  	console.log("ajaxComplete");
	});


	jQuery(document).ready(function(){
		console.log("ready");
	// Hide Header on on scroll down
	    var didScroll;
	    var lastScrollTop = 0;
	    var delta = 5;
	    var navbarHeight = jQuery('.page-header').outerHeight();

	    jQuery(window).scroll(function(event){
	        didScroll = true;
	    });

	    setInterval(function() {
	        if (didScroll) {
	            hasScrolled();
	            didScroll = false;
	        }
	    }, 250);

	    function hasScrolled() {
	        var st = jQuery(this).scrollTop();

	        // Make sure they scroll more than delta
	        if(Math.abs(lastScrollTop - st) <= delta)
	            return;

	        // If they scrolled down and are past the navbar, add class .nav-up.
	        // This is necessary so you never see what is "behind" the navbar.
	        if (st > lastScrollTop && st > navbarHeight){
	            // Scroll Down
	            jQuery('.page-header').removeClass('nav-down').addClass('nav-up');
	        } else {
	            // Scroll Up
	            if(st + jQuery(window).height() < jQuery(document).height()) {
	                jQuery('.page-header').removeClass('nav-up').addClass('nav-down');
	            }
	        }
	        lastScrollTop = st;
	    }
	});

	
	jQuery(document).load(function(){
		console.log("load");
	});


	jQuery(window).on("scroll", function(){
		console.log("scroll");
		if(jQuery(window).scrollTop() > 300){
			jQuery("#scroll-to-top").addClass("show");
		}else{
			jQuery("#scroll-to-top").removeClass("show");
		}
		
	});
});