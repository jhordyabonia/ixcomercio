require([
    'jquery'
],
function ($, Component) {
    'use strict';
    $(document).ready(function(){
		$('#scroll-to-top').click(function(){
			$("html, body").animate({scrollTop: 0}, 600, "easeOutCubic");
			return false;
		});


		jQuery(".SOLburger").on('click', function(){
			menuToggle();
			jQuery(this).toggleClass("is-clicked");
		});


		function menuToggle(){
			if(jQuery('.SOLburger').hasClass("is-clicked")){
				jQuery('#sideMenu').hide();
				jQuery('#sideMenu').animate({right: '-100%'}, 500);
			}else{
				jQuery('#sideMenu').show();
				jQuery('#sideMenu').animate({right:0}, 500);
			}
		}
		

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


	$(document).ajaxComplete(function() {
		jQuery(".product-essential .swatch-attribute-options .swatch-option").hover(function(){
			var labelColor = jQuery(this).attr("option-label");
			jQuery(".swatch-attribute-selected-option").text(labelColor);
		}, function(){
			jQuery(".swatch-attribute-selected-option").text(jQuery(".product-essential .swatch-attribute-options .swatch-option.selected").attr("option-label"));
		});		
	});


	jQuery(window).on("scroll", function(){
		if(jQuery(window).scrollTop() > 300){
			jQuery("#scroll-to-top").addClass("show");
		}else{
			jQuery("#scroll-to-top").removeClass("show");
		}
		
	});

	return Component.extend({});
});