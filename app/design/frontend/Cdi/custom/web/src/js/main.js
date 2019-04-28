var $ = jQuery;

$(document).ready(function(){

	if($(".product-options-wrapper .swatch-attribute.jam_color").length){
		$(".product-options-wrapper .swatch-attribute.jam_color .swatch-attribute-options .swatch-option").hover(function(){
			var labelColor = $(this).attr("option-label");
			$(".swatch-attribute-selected-option").text(labelColor);
		}, function(){
			$(".swatch-attribute-selected-option").text($(".product-options-wrapper .swatch-attribute.jam_color .swatch-attribute-options .swatch-option.selected").attr("option-label"));
		})
	}

});

var windowScroll_t;
$(window).scroll(function(){
	clearTimeout(windowScroll_t);
	windowScroll_t = setTimeout(function() {
		if ($(this).scrollTop() > 100){
			$('#scroll-to-top').addClass("show");
		}else{
			$('#scroll-to-top').removeClass("show");
		}
	}, 500);
});
$('#scroll-to-top').click(function(){
	$( "html, body" ).animate({scrollTop:0}, 500, 'swing');
});
