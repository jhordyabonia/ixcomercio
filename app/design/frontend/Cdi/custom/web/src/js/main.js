
jQuery(document).ready(function(){

	if(jQuery(".product-options-wrapper .swatch-attribute.jam_color").length){
		jQuery(".product-options-wrapper .swatch-attribute.jam_color .swatch-attribute-options .swatch-option").hover(function(){
			var labelColor = jQuery(this).attr("option-label");
			jQuery(".swatch-attribute-selected-option").text(labelColor);
		}, function(){
			jQuery(".swatch-attribute-selected-option").text(jQuery(".product-options-wrapper .swatch-attribute.jam_color .swatch-attribute-options .swatch-option.selected").attr("option-label"));
		})
	}

});

var windowScroll_t;
jQuery(window).scroll(function(){
	clearTimeout(windowScroll_t);
	windowScroll_t = setTimeout(function() {
		if (jQuery(this).scrollTop() > 100){
			jQuery('#scroll-to-top').addClass("show");
		}else{
			jQuery('#scroll-to-top').removeClass("show");
		}
	}, 500);
});
jQuery('#scroll-to-top').click(function(){
	jQuery( "html, body" ).animate({scrollTop:0}, 500, 'swing');
});