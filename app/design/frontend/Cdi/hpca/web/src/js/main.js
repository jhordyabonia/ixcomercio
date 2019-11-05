require(['jquery', 'owlCarouselJs', 'mainJs', 'domReady!'], function($) {
    
	jQuery(document).ready(function() {

		jQuery('#scroll-to-top').click(function(){
			jQuery("html, body").animate({scrollTop: 0}, 600, "easeOutCubic");
			return false;
		});


		// =============================================
	    // Height catalog items list
	    // =============================================

	    if($(".products-grid .product-items").length){
	      var list = $(".products-grid .product-items > .item");
	      var listImage = $(list).find(".product-image-wrapper");
	      var listName = $(list).find(".product-name");
	      var listFamily = $(list).find(".atributo-familia");
	      var listPrice = $(list).find(".price-box");
	      var arrayList = [];
	      var arrayImage = [];
	      var arrayName = [];
	      var arrayFamily = [];
	      var arrayPrice = [];

	      setTimeout(function(){
	        //Image
	        jQuery.each(listImage, function(i, val){
	          arrayImage.push(jQuery(val).innerHeight());
	        });
	        Math.max.apply(Math,arrayImage);
	        jQuery(listImage).css("minHeight", Math.max.apply(Math,arrayImage)+"px");

	        //Name
	        jQuery.each(listName, function(i, val){
	          arrayName.push(jQuery(val).innerHeight());
	        });
	        Math.max.apply(Math,arrayName);
	        jQuery(listName).css("minHeight", Math.max.apply(Math,arrayName)+"px");

	        //Family
	        jQuery.each(listFamily, function(i, val){
	          arrayFamily.push(jQuery(val).innerHeight());
	        });
	        Math.max.apply(Math,arrayFamily);
	        jQuery(listFamily).css("minHeight", Math.max.apply(Math,arrayFamily)+"px");

	        //Price
	        jQuery.each(listPrice, function(i, val){
	          arrayPrice.push(jQuery(val).innerHeight());
	        });
	        Math.max.apply(Math,arrayPrice);
	        jQuery(listPrice).css("minHeight", Math.max.apply(Math,arrayPrice)+"px");

	        //Item
	        jQuery.each(list, function(i, val){
	          arrayList.push(jQuery(val).innerHeight());
	        });
	        Math.max.apply(Math,arrayList);
	        jQuery(list).css("height", Math.max.apply(Math,arrayList)+"px");
	      },500);
	    }


		// =============================================
	    // Quantity Controls
	    // =============================================
	    
	    var qtyControl = jQuery('.control-qty');

	    qtyControl.on('click', function (e) {
	      var self = jQuery(this);
	      var parent = self.parent();
	      var qtyField = parent.find('input.input-text.qty');
	      var qtyVal = parseInt(qtyField.val());

	      if(self.hasClass('remove')){ 
	        if(qtyVal >= 2 ){
	          qtyField.val(qtyVal-1);
	          if(qtyVal == 2){
	            self.addClass('disable');
	          }
	        }
	      }else if(self.hasClass('add')){
	        qtyField.val(qtyVal +1);
	        parent.find('.remove').removeClass('disable');
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


	    // =============================================
	    // Add * to input required
	    // =============================================

	    var required = $('input.required-entry, select.required-entry');
	    $.each(required, function(i, val){
	      if(!$(val).parents('.field').hasClass('required')){
	        $(val).parents('.field').addClass('required');
	      }
	    });


		jQuery('.icon-search-button').click(function(){
			jQuery('.block-search').toggleClass("open");
			jQuery(this).toggleClass("close");
		});

		jQuery('#iconBurgerButton').click(function(){
			jQuery('header.page-header .wrapper-nav .nav-sections').toggleClass("open");
			jQuery(this).toggleClass("close");
		});

		if(jQuery('.products-grid .owl-carousel').length){
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
		}


		// =============================================
	    // Get states
	    // =============================================
	    
	    var fieldState = $('form .fieldset > .field.region #region_id');
	    var stateOptions;
	    var intervalState;
	    /*
	    function getStates(){
	      $.ajax({
	        url: '/places/search/',
	        type: 'GET',
	        dataType: 'json',
	        success: function(res) {
	          $.each(stateOptions, function(i, val){
	            var optionName = $(val).text();
	            $.each(res, function(iRes, valRes){
	              if(valRes.Name == optionName){
	                $(val).attr("parentId", valRes.Id);
	                $(val).show();
	              }
	            });
	          });
	        }
	      });      
	    }

	    if($(fieldState).length){
	      intervalState = setInterval(function(){
	        stateOptions = $(fieldState).find('option');
	        if($(stateOptions).length >= 2){
	          getStates();
	          clearInterval(intervalState);
	        }
	      }, 1000);
	    }
	    */


	    function getStates2(){
			$.ajax({
			    url: '/places/search/',
			    type: 'GET',
			    dataType: 'json',
			    success: function(res) {
			        $.each(res, function(iRes, valRes){
			        	$(fieldState).append("<option value='' parentid='"+valRes.Id+"''>"+valRes.Name+"</option>");
			        });
			        $(fieldState).show();
			        $(fieldState).attr("disabled", false);
			        $("input#region").hide();
			    }
			});
	    }

	    if($(fieldState).length){
	      intervalState = setInterval(function(){
	        stateOptions = $(fieldState).find('option');
	        if($(stateOptions).length >= 1){
	          getStates2();
	          clearInterval(intervalState);
	        }
	      }, 1000);
	    }
	    
	    var fieldCountry = $('form .fieldset > .field.country #country');
	    $( fieldCountry ).change(function() {
		  	getStates2();
		});



		// =============================================
	    // Get cities
	    // =============================================
	    
	    var fieldCity = $('form .fieldset > .field.city #city_id');

	    fieldState.on('change', function (e) {
	      	$.ajax({
		        url: '/places/search/',
		        data: 'parentId='+fieldState.find('option:selected').attr('parentId'),
		        type: 'GET',
		        dataType: 'json',
		        success: function(res) {
			        $(fieldCity).find('option:not([value=""])').remove();
			        $.each(res, function(i, val){
			            $(fieldCity).append("<option value='"+val.Id+"'>"+val.Name+"</option>");
			    	});
		        }
	      	});

	      	var valState = $(fieldState).find('option:selected');
			$(fieldState).parent().find('input').val($(valState).text());
			$(fieldState).parent().find('input').keyup();
	    });


	    // =============================================
	    // Print select Address checkout
	    // =============================================
	    var fieldCityCheckout;
	    function getStatesCheckout(){
	    	fieldCityCheckout = $('form .fieldset > .field[name="shippingAddress.city"] .control');

	    	$.ajax({
			    url: '/places/search/',
			    type: 'GET',
			    dataType: 'json',
			    success: function(res) {
			    	$(fieldStateCheckout).find('input').hide();
			    	$(fieldCityCheckout).find('input').hide();
			    	var html = '<select id="fieldStateCheckout" class="select" name="state_id" aria-required="true" aria-invalid="false">'+
	    					'<option data-title="" value="">Please select a region, state or province.</option>';

			        $.each(res, function(iRes, valRes){
			        	html += "<option value='' parentid='"+valRes.Id+"''>"+valRes.Name+"</option>";
			        });

			        html += '</select>';

	    			$(fieldStateCheckout).append(html);

	    			var htmlCities = '<select id="fieldCityCheckout" class="select" name="cities_id" aria-required="true" aria-invalid="false">'+
	    							'<option data-title="" value="">Please select a city.</option>'+
	    							'</select>';
	    			$(fieldCityCheckout).append(htmlCities);


	    			// =============================================
				    // Print select City checkout
				    // =============================================
				    $('#fieldStateCheckout').on('change', function (e) {
				    	$.ajax({
							url: '/places/search/',
							data: 'parentId='+$('#fieldStateCheckout').find('option:selected').attr('parentId'),
							type: 'GET',
							dataType: 'json',
							success: function(res) {
							  $(fieldCityCheckout).find('select option:not([value=""])').remove();
							  $.each(res, function(i, val){
							    $(fieldCityCheckout).find('select').append("<option value='"+val.Id+"'>"+val.Name+"</option>");
							  });
							}
						});


						var valState = $(fieldStateCheckout).find('select option:selected');
						$(fieldStateCheckout).find('input').val($(valState).text());
						$(fieldStateCheckout).find('input').keyup();
					    
				    });

				    $('#fieldCityCheckout').on('change', function (e) {
						var valCity = $(fieldCityCheckout).find('select option:selected');
						$(fieldCityCheckout).find('input').val($(valCity).text());
						$(fieldCityCheckout).find('input').keyup();
				    });
			    }
			});
	    }

	    if (window.location.href.indexOf("checkout") > -1) {
	    	var fieldStateCheckout;
	    	intervalState = setInterval(function(){
    			fieldStateCheckout = $('form .fieldset > .field[name="shippingAddress.region"] .control');
    			if($(fieldStateCheckout).length >= 1){
		        	getStatesCheckout();
		          	clearInterval(intervalState);
		        }
	      	}, 1000);
	    }
	
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