require(['jquery', 'mainJs', 'domReady!'], function($) {
    
	jQuery(".product-options-wrapper .swatch-attribute.jam_color .swatch-attribute-options .swatch-option").hover(function(){
		var labelColor = jQuery(this).attr("option-label");
		jQuery(".swatch-attribute-selected-option").text(labelColor);
	}, function(){
		jQuery(".swatch-attribute-selected-option").text(jQuery(".product-options-wrapper .swatch-attribute.jam_color .swatch-attribute-options .swatch-option.selected").attr("option-label"));
	});

	
	jQuery('#scroll-to-top').click(function(){
		jQuery("html, body").animate({scrollTop: 0}, 600, "easeOutCubic");
		return false;
	});


	$(document).ajaxSuccess(function() {
	  console.log("carga ajax");
	});


	jQuery(window).on("scroll", function(){
		if(jQuery(window).scrollTop() > 300){
			jQuery("#scroll-to-top").addClass("show");
		}else{
			jQuery("#scroll-to-top").removeClass("show");
		}
		
	});
});