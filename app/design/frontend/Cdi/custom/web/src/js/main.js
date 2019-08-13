require([
    'jquery'
],
function ($, Component) {
    'use strict';
    
	jQuery('#scroll-to-top').click(function(){
		jQuery("html, body").animate({scrollTop: 0}, 600, "easeOutCubic");
		return false;
	});


	$(document).ready(function(){
		
		// =============================================
	    // Skip Links
	    // =============================================

	    var skipContents = jQuery('.skip-content');
	    var skipLinks = jQuery('.skip-link');

	    skipLinks.on('click', function (e) {
	        e.preventDefault();

	        var self = jQuery(this);
	        // Use the data-target-element attribute, if it exists. Fall back to href.
	        var target = self.attr('data-target-element') ? self.attr('data-target-element') : self.attr('href');

	        // Get target element
	        var elem = jQuery(target);

	        // Check if stub is open
	        var isSkipContentOpen = elem.hasClass('skip-active') ? 1 : 0;

	        // Hide all stubs
	        skipLinks.removeClass('skip-active');
	        skipContents.removeClass('skip-active');

	        // Toggle stubs
	        if (isSkipContentOpen) {
	            self.removeClass('skip-active');
	        } else {
	            self.addClass('skip-active');
	            elem.addClass('skip-active');
	        }
	    });


	    // =============================================
	    // Quantity Controls
	    // =============================================
		var qtyControl = jQuery('.control-qty');

		qtyControl.on('click', function (e) {

			var self = jQuery(this);
			var parent = jQuery(this).parent();
			var QtyVal = parent.find('input.input-text.qty').val();

			if(self.hasClass('remove')){
				if(QtyVal >= 2 ){
					QtyVal.val(QtyVal--);
					if(QtyVal == 2){
						self.addClass('disable');
					}
				}
			}else if(self.hasClass('add')){
				QtyVal.val(QtyVal++);
				parent.find('.remove').removeClass('disable');
			}
		});



	});


	$(document).ajaxComplete(function() {
	  	jQuery(".product-options-wrapper .swatch-attribute.jam_color .swatch-attribute-options .swatch-option").hover(function(){
			var labelColor = jQuery(this).attr("option-label");
			jQuery(".swatch-attribute-selected-option").text(labelColor);
		}, function(){
			jQuery(".swatch-attribute-selected-option").text(jQuery(".product-options-wrapper .swatch-attribute.jam_color .swatch-attribute-options .swatch-option.selected").attr("option-label"));
		});
	});


	jQuery(window).on("scroll", function(){
		if(jQuery(window).scrollTop() > 300){
			jQuery("#scroll-to-top").addClass("show");
		}else{
			jQuery("#scroll-to-top").removeClass("show");
		}
		
	});

	return Component;
});