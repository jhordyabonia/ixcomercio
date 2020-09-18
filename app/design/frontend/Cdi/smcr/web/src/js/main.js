require(['jquery', 'owlCarouselJs', 'mainJs', 'domReady!'], function($) {
    
	jQuery(document).ready(function() {

		var w_width = $( window ).width();
		var w_height = $( window ).height();
		
		jQuery('#scroll-to-top').click(function(){
			jQuery("html, body").animate({scrollTop: 0}, 600, "easeOutCubic");
			return false;
		});


		// =============================================
	    // Height catalog items list
	    // =============================================

	    if($(".products-grid .product-items").length){
	      var list = $(".products-grid .product-items .item");
	      var listImage = $(list).find(".product-image-wrapper");
	      var listName = $(list).find(".product-item-name");
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
	    // Add to wishlist product detail
	    // =============================================

	    if($('.catalog-product-view .product-addto-links').length){
	    	var wlistButton = $('.catalog-product-view .product-addto-links');
	    	wlistButton.remove();
	    	$('.product-info_main .product-add-form .box-tocart .actions').append(wlistButton);
	    }


	    // =============================================
	    // Move email option - product detail
	    // =============================================
	    if($('.catalog-product-view .product-info_main .ept-social-share').length){
	    	if($('.catalog-product-view .product-info_main .product-social-links').length){
	    		var html = $('.catalog-product-view .product-info_main .product-social-links');
	    		$('.catalog-product-view .product-info_main .ept-social-share').append(html);
	    	}
	    }


	    // =============================================
	    // Footer Mobile
	    // =============================================

	    $("footer .footer-nav .link-block h4").click(function(){
	    	if(w_width <= 992){
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

		

		// =============================================
	    // Toggle menu mobile
	    // =============================================

		$('#iconBurgerButton').click(function(){
			$('header.page-header .wrapper-nav .nav-sections').toggleClass("open");
			$('header .wrapper-nav .nav-sections').css('minHeight', w_height);
			$(this).toggleClass("close");

			setTimeout(function(){
				$('header .wrapper-nav .nav-sections .nav-sections-items').toggleClass("open");
			},200);
		});

		// =============================================
	    // Toggle submenu mobile
	    // =============================================
		$("header .nav-sections .navigation ul.ui-menu .level0.parent > .level-top > .ui-menu-icon").click(function(e){
			e.preventDefault();
			if(w_width <= 992){
				$(this).parent().parent().find(' > .submenu').slideToggle();
			}
		});



		if($('.products-grid .owl-carousel').length){
			$('.products-grid .owl-carousel').owlCarousel({
				nav: true,
				dots: true,
				navSpeed: 800,
				loop: true,
				margin: 30,
				responsive:{
					0:{
						items: 1
					},
					650:{
				      	items: 3
			      	},
			    	992:{
				      	items: 4
			      	}
			    }
			});
		}



		// =============================================
	    // Get states
	    // =============================================
	    if($('.header-account-mobile').length){
	    	var menuAccountMobile = $('.header-account-mobile');
	    	var parent = $('header.page-header .header-wrapper-nav .wrapper-nav .nav-sections .nav-sections-items');

	    	$(parent).append(menuAccountMobile);
	    }



	

		// =============================================
	    // Get states
	    // =============================================

	    var fieldState = $('form .fieldset > .field.region #region_id');
	    var stateOptions;
	    var intervalState;

	    function getStates(){
	    	$('body').trigger('processStart');
	    	$.ajax({
			    url: '/places/search/',
			    type: 'GET',
			    dataType: 'json',
			    success: function(res) {
			    	$(fieldState).find('option:not([value=""])').remove();
			    	$.each(res, function(iRes, valRes){
			    		$(fieldState).append("<option value='"+valRes.Id+"' parentid='"+valRes.Id+"'>"+valRes.Name+"</option>");
			        });
			        $(fieldState).show();
			        $(fieldState).attr("disabled", false);
			        $("input#region").hide();

			        $('body').trigger('processStop');
			    }
			});
	    }

	    if($(fieldState).length){
	      intervalState = setInterval(function(){
	        stateOptions = $(fieldState).find('option');
	        if($(stateOptions).length >= 1){
	          getStates();
	          clearInterval(intervalState);
	        }
	      }, 1000);
	    }
	    
	    var fieldCountry = $('form .fieldset > .field.country #country');
	    $( fieldCountry ).change(function() {
		  	getStates();
		});


		// =============================================
	    // Get cities
	    // =============================================

	    var fieldCity = $('form .fieldset > .field.city #city_id');
	    var fieldZoneStreet = $('form .fieldset > .field.street-zone .control');
	    /*
	    var fieldStreet = $('form .fieldset > .field.street .control .nested .additional .control');
	    var htmlStreet = '<select id="fieldSelectStreet" class="select" name="street2_id" aria-required="true" aria-invalid="false">'+
						'<option data-title="" value="">Please select a zone.</option>'+
						'</select>';
		$(fieldStreet).append(htmlStreet);
	    $(fieldStreet).find('input').hide();*/

	    fieldState.on('change', function (e) {
	    	$('body').trigger('processStart');
	      	$.ajax({
		        url: '/places/search/',
		        data: 'parentId='+fieldState.find('option:selected').attr('parentId'),
		        type: 'GET',
		        dataType: 'json',
		        success: function(res) {
			        $(fieldCity).find('option:not([value=""])').remove();
			        $.each(res, function(i, val){
			            $(fieldCity).append("<option value='"+val.Id+"' parentId='"+val.Id+"'>"+val.Name+"</option>");
			    	});

			    	$('body').trigger('processStop');
		        }
	      	});

	      	var valState = $(fieldState).find('option:selected');
			$(fieldState).parent().find('input').val($(valState).text());
			$(fieldState).parent().find('input').keyup();
	    });


	    // =============================================
	    // Print select street
	    // =============================================

	    $('#city_id').on('change', function (e) {
	    	$('body').trigger('processStart');
	    	$.ajax({
				url: '/places/search/',
				data: 'parentId='+$('#city_id').find('option:selected').attr('parentId'),
				type: 'GET',
				dataType: 'json',
				success: function(resCity) {
					$(fieldZoneStreet).find('select option:not([value=""])').remove();
				  	$.each(resCity, function(iResCity, valResCity){
				    	$(fieldZoneStreet).find('select').append("<option value='"+valResCity.ParentId+"' parentId='"+valResCity.ParentId+"' postalCode='"+valResCity.PostalCode+"'>"+valResCity.Name+"</option>");
				  	});

				  	$('body').trigger('processStop');
				}
			});
	    });


	    // =============================================
	    // Print postal code
	    // =============================================

	    $('#fieldSelectStreet').on('change', function (e) {
	    	$('body').trigger('processStart');
	    	var valStreet = $('#fieldSelectStreet').find('option:selected');
			$(fieldStreet).find('input').val($(valStreet).text());
			$(fieldStreet).find('input').keyup();

	    	$('#zip').val($(valStreet).attr('postalCode'));
	    	$('#zip').find('input').keyup();
	    	$('body').trigger('processStop');
	    });




	    // =============================================
	    // Print select Address checkout
	    // =============================================
	    var fieldCityCheckout;
	    function getStatesCheckout(){

	    	$('body').trigger('processStart');
	    	
	    	var fieldStreetCheckout = $('form .fieldset > .field.street .control .additional .control');
	    	var fieldZoneCheckout = $('form .fieldset > .field select[name="custom_attributes[zone_id]"]');
		    /*var htmlStreetCheckout = '<select id="fieldSelectStreet" class="select" name="street2_id" aria-required="true" aria-invalid="false">'+
							'<option data-title="" value="">Please select a zone.</option>'+
							'</select>';*/
			//$(fieldStreetCheckout).append(htmlStreetCheckout);
		    $(fieldStreetCheckout).find('input').hide();

	    	fieldCityCheckout = $('form .fieldset > .field[name="shippingAddress.city"] .control');

	    	$.ajax({
			    url: '/places/search/',
			    type: 'GET',
			    dataType: 'json',
			    success: function(res) {
			    	if($(fieldStateCheckout).find('input').length){
			    		$(fieldStateCheckout).find('input').hide();

			    		var html = '<select id="fieldStateCheckout" class="select" name="state_id" aria-required="true" aria-invalid="false">'+
	    					'<option data-title="" value="">Please select a region, state or province.</option>';

				        $.each(res, function(iRes, valRes){
				        	html += "<option value='' parentid='"+valRes.Id+"''>"+valRes.Name+"</option>";
				        });

				        html += '</select>';

		    			$(fieldStateCheckout).append(html);
			    	}else{
			    		var stateOptions = $(fieldStateCheckout).find('select option');
			    		$.each(stateOptions, function(iOpt, valOpt){
				            var optionName = $(valOpt).text();
				            $.each(res, function(iRes, valRes){
				            	if(valRes.Name == optionName){
				                	$(valOpt).attr("parentId", valRes.Id);
				                	$(valOpt).show();
				              	}
				            });
				        });
			    	}
			    	
			    	$(fieldCityCheckout).find('input').hide();
			    	var htmlCities = '<select id="fieldCityCheckout" class="select" name="cities_id" aria-required="true" aria-invalid="false">'+
	    							'<option data-title="" value="">Please select a city.</option>'+
	    							'</select>';
	    			$(fieldCityCheckout).append(htmlCities);

	    			$('body').trigger('processStop');


	    			// =============================================
				    // Print select City checkout
				    // =============================================
				    var selectStateCheckout = $(fieldStateCheckout).find('select');
				    $(selectStateCheckout).on('change', function (e) {
				    	$('body').trigger('processStart');
				    	$.ajax({
							url: '/places/search/',
							data: 'parentId='+$(selectStateCheckout).find('option:selected').attr('parentId'),
							type: 'GET',
							dataType: 'json',
							success: function(resState) {
								console.log(resState);
							  $(fieldCityCheckout).find('select option:not([value=""])').remove();
							  $.each(resState, function(iState, valState){
							    $(fieldCityCheckout).find('select').append("<option value='"+valState.Id+"' parentId='"+valState.Id+"'>"+valState.Name+"</option>");
							  });

							  $('body').trigger('processStop');
							}
						});


						var valueState = $(fieldStateCheckout).find('select option:selected');
						$(fieldStateCheckout).find('input').val($(valueState).text());
						$(fieldStateCheckout).find('input').keyup();
					    
				    });


				    // =============================================
				    // Print select street checkout
				    // =============================================
				    $('#fieldCityCheckout').on('change', function (e) {
				    	$('body').trigger('processStart');
						var valCity = $(fieldCityCheckout).find('select option:selected');
						$(fieldCityCheckout).find('input').val($(valCity).text());
						$(fieldCityCheckout).find('input').keyup();

						$.ajax({
							url: '/places/search/',
							data: 'parentId='+$('#fieldCityCheckout').find('option:selected').attr('parentId'),
							type: 'GET',
							dataType: 'json',
							success: function(resCity) {
								$(fieldZoneCheckout).find('option').remove();
                  				$(fieldZoneCheckout).append('<option data-title="" value="" selected>Please select a zone.</option>');
							  	$.each(resCity, function(iResCity, valResCity){
							    	$(fieldZoneCheckout).append("<option value='"+valResCity.ParentId+"' parentId='"+valResCity.ParentId+"' postalCode='"+valResCity.PostalCode+"'>"+valResCity.Name+"</option>");
							  	});

							  	$('body').trigger('processStop');
							}
						});
				    });


				    // =============================================
				    // Print postal code
				    // =============================================

				    $('select[name="custom_attributes[zone_id]"]').on('change', function (e) {
				    	$('body').trigger('processStart');
				    	var valStreetCheckout = $('select[name="custom_attributes[zone_id]"]').find('option:selected');
						$(fieldStreetCheckout).find('input').val($(valStreetCheckout).text());
						$(fieldStreetCheckout).find('input').keyup();

				    	$('input[name="postcode"]').val($(valStreetCheckout).attr('postalCode'));
				    	$('input[name="postcode"]').keyup();
				    	$('body').trigger('processStop');
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



	    // =============================================
	    // Zendesk link - footer
	    // =============================================
	    var linksFooter = $('footer a');

	    $.each(linksFooter, function(i, val){
	    	if(val.innerText == "Zendesk Support" || val.innerText == "Preguntas frecuentes"){
	        	var parentLi = $(this).parent();
	        	$('footer .col-md-3:eq(2) ul').append(parentLi);
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
