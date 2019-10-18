require(['jquery', 'owlCarouselJs', 'mainJs', 'domReady!'], function($) {
    
	jQuery(document).ready(function() {
		jQuery('#scroll-to-top').click(function(){
			jQuery("html, body").animate({scrollTop: 0}, 600, "easeOutCubic");
			return false;
		});

		jQuery('.icon-search-button').click(function(){
			jQuery('.block-search').toggleClass("open");
			jQuery(this).toggleClass("close");
		});

		jQuery('#iconBurgerButton').click(function(){
			jQuery('header.page-header .wrapper-nav .nav-sections').toggleClass("open");
			jQuery(this).toggleClass("close");
		});

		jQuery('.products-grid .owl-carousel').owlCarousel({
			nav: true,
			dots: true,
			navSpeed: 800,
			loop: true,
			margin: 30,
			responsive:{
				0:{
					items: 2,
					nav: false,
					dots: false
				},
	      991:{
	      	items: 4,
	        nav: true,
	        dots: true
	      }
	    }
		});


		// =============================================
	    // Footer Mobile
	    // =============================================

	    $("footer .footer-nav .link-block h4").click(function(){
	      if($(this).hasClass("open")){
	        $(this).removeClass("open");
	        $(this).find('.icon').removeClass('icon-up-open').addClass('icon-down-open');
	        $(this).find('.icon').html('&#xe82c;');
	        $(this).parent().find("ul").slideUp();
	      }else{
	        $('footer .footer-nav .link-block h4').removeClass('open');
	        $('footer .footer-nav .link-block ul').slideUp();
	        $('footer .footer-nav .link-block h4 .icon').removeClass('icon-up-open').addClass('icon-down-open');
	        $('footer .footer-nav .link-block h4 .icon').html('&#xe82c;');
	        $(this).addClass('open');
	        $(this).find('.icon').removeClass('icon-down-open').addClass('icon-up-open');
	        $(this).find('.icon').html('&#xe82f;');
	        $(this).parent().find('ul').slideDown();
	      }
	    });
	});

	
	var list = jQuery(".search.results .products-grid .product-items .item");
	var arrayList = [];
	jQuery.each(list, function(i, val){
		arrayList.push(jQuery(val).innerHeight());
		console.log(jQuery(val).innerHeight());
	});
	Math.max.apply(Math,arrayList);
	jQuery(list).css("height", Math.max.apply(Math,arrayList)+"px");


	jQuery(window).on("scroll", function(){
		if(jQuery(window).scrollTop() > 300){
			jQuery("#scroll-to-top").addClass("show");
		}else{
			jQuery("#scroll-to-top").removeClass("show");
		}
		
	});
});