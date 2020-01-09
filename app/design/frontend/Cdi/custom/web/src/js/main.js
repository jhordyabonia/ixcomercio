require([
    'jquery'
],
function ($, Component) {
    'use strict';
    
	jQuery('#scroll-to-top').click(function(){
		jQuery("html, body").animate({scrollTop: 0}, 600, "easeOutCubic");
		return false;
	});

	var w_width = jQuery( window ).width();

	$(document).ready(function(){

		// =============================================
	    // Menu mobile append elements
	    // =============================================
	    var htmlCartWrapper = $('.account-cart-wrapper');
	    var htmlSearchWrapper = $('header .block-search');

	    if(w_width <= 768){
	    	$('header > .nav-sections').addClass('skip-content');
	    	$(htmlSearchWrapper).addClass('skip-content');

	    	$('.skip-links-wrapper').append($(htmlCartWrapper).html());
	    	$('header').append(htmlSearchWrapper);

	    	var htmlAccountWrapper = $('.skip-links-wrapper #header-account');

	    	$('header').append(htmlAccountWrapper);

	    	$('.account-cart-wrapper').remove();

	    	$('header .skip-links-wrapper .minicart-wrapper a.showcart, header .skip-links-wrapper .dropdown-toggle.banderas').click(function(){
				$('header .skip-links-wrapper .skip-link').removeClass('skip-active');
				$('header .skip-links-wrapper .minicart-wrapper a.showcart').removeClass('active');
			});
	    }
	
		
		// =============================================
	    // Skip Links
	    // =============================================
	    var skipContents = $('.skip-content');
	    var skipLinks = $('.skip-link');

	    skipLinks.on('click', function (e) {
	        e.preventDefault();

	        var self = $(this);
	        // Use the data-target-element attribute, if it exists. Fall back to href.
	        var target = self.attr('data-target-element') ? self.attr('data-target-element') : self.attr('href');

	        // Get target element
	        var elem = $(target);

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
	    /*
	    var skipContents = $('header > div');
	    var skipLinks = $('.skip-link');
	    
	    skipLinks.on('click', function (e) {
	      e.preventDefault();
	      var self = $(this);
	      var target = self.attr('href');
	      //Get target element
	      var elem = $(target);
	      //Check if stub is open
	      var isSkipContentOpen = elem.hasClass('skip-content skip-active') ? 1 : 0;
	      //Hide all stubs
	      skipLinks.removeClass('skip-active');
	      skipContents.removeClass('skip-content skip-active');
	      //Toggle stubs
	      if (isSkipContentOpen) {
	        self.removeClass('skip-content skip-active');
	      }else{
	        self.addClass('skip-active');
	        elem.addClass('skip-content skip-active');
	      }
	    });
	    */


	    // =============================================
	    // Height catalog items list
	    // =============================================

	    if($(".products-grid .product-items").length){
	      var list = $(".products-grid .product-items > .item");
	      var listImage = $(list).find(".product-image-wrapper");
	      var listName = $(list).find(".product-item-name");
	      var listFamily = $(list).find(".atributo-familia");
	      var listReviews = $(list).find(".product-reviews-summary");
	      var listPrice = $(list).find(".price-box");
	      var listOptions = $(list).find(".swatch-opt");
	      var arrayList = [];
	      var arrayImage = [];
	      var arrayName = [];
	      var arrayFamily = [];
	      var arrayReviews = [];
	      var arrayPrice = [];
	      var arrayOptions = [];

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
	        jQuery.each(listReviews, function(i, val){
	          arrayReviews.push(jQuery(val).innerHeight());
	        });
	        Math.max.apply(Math,arrayReviews);
	        jQuery(listReviews).css("minHeight", Math.max.apply(Math,arrayReviews)+"px");

	        //Price
	        jQuery.each(listPrice, function(i, val){
	          arrayPrice.push(jQuery(val).innerHeight());
	        });
	        Math.max.apply(Math,arrayPrice);
	        jQuery(listPrice).css("minHeight", Math.max.apply(Math,arrayPrice)+"px");

	        //Color
	        jQuery.each(listOptions, function(i, val){
	          arrayOptions.push(jQuery(val).innerHeight());
	        });
	        Math.max.apply(Math,arrayOptions);
	        jQuery(listOptions).css("minHeight", Math.max.apply(Math,arrayOptions)+"px");

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
	    // Get states
	    // =============================================

	    var fieldState = $('form .fieldset > .field.region #region_id');
	    var stateOptions;
	    var intervalState;

	    function getStates(){
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
	    $(fieldStreet).find('input').hide();
	    */

	    fieldState.on('change', function (e) {
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
				}
			});
	    });


	    // =============================================
	    // Print postal code
	    // =============================================

	    $('#fieldSelectStreet').on('change', function (e) {
	    	var valStreet = $('#fieldSelectStreet').find('option:selected');
			$(fieldStreet).find('input').val($(valStreet).text());
			$(fieldStreet).find('input').keyup();

	    	$('#zip').val($(valStreet).attr('postalCode'));
	    	$('#zip').find('input').keyup();
	    });




	    // =============================================
	    // Print select Address checkout
	    // =============================================
	    var fieldCityCheckout;
	    function getStatesCheckout(){
	    	var fieldStreetCheckout = $('form .fieldset > .field.street .control .additional .control');
		    var htmlStreetCheckout = '<select id="fieldSelectStreet" class="select" name="street2_id" aria-required="true" aria-invalid="false">'+
							'<option data-title="" value="">Please select a zone.</option>'+
							'</select>';
			$(fieldStreetCheckout).append(htmlStreetCheckout);
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

		    			setTimeout(function(){
		    				if($('.field[name="shippingAddress.region_id"] select').val() != ""){
					    		$('.field[name="shippingAddress.region_id"] select').trigger('change');
					    	}	
		    			},500);
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

				        setTimeout(function(){
		    				if($('.field[name="shippingAddress.region_id"] select').val() != ""){
					    		$('.field[name="shippingAddress.region_id"] select').trigger('change');
					    	}	
		    			},500);
			    	}

			    	$(fieldCityCheckout).find('input').hide();
			    	var htmlCities = '<select id="fieldCityCheckout" class="select" name="cities_id" aria-required="true" aria-invalid="false">'+
	    							'<option data-title="" value="">Please select a city.</option>'+
	    							'</select>';
	    			$(fieldCityCheckout).append(htmlCities);


	    			// =============================================
				    // Print select City checkout
				    // =============================================
				    var selectStateCheckout = $(fieldStateCheckout).find('select');
				    $(selectStateCheckout).on('change', function (e) {
				    	console.log("change state");
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
						var valCity = $(fieldCityCheckout).find('select option:selected');
						$(fieldCityCheckout).find('input').val($(valCity).text());
						$(fieldCityCheckout).find('input').keyup();

						$.ajax({
							url: '/places/search/',
							data: 'parentId='+$('#fieldCityCheckout').find('option:selected').attr('parentId'),
							type: 'GET',
							dataType: 'json',
							success: function(resCity) {
								$(fieldStreetCheckout).find('select option:not([value=""])').remove();
							  	$.each(resCity, function(iResCity, valResCity){
							    	$(fieldStreetCheckout).find('select').append("<option value='"+valResCity.ParentId+"' parentId='"+valResCity.ParentId+"' postalCode='"+valResCity.PostalCode+"'>"+valResCity.Name+"</option>");
							  	});
							}
						});
				    });


				    // =============================================
				    // Print postal code
				    // =============================================

				    $('#fieldSelectStreet').on('change', function (e) {
				    	var valStreetCheckout = $('#fieldSelectStreet').find('option:selected');
						$(fieldStreetCheckout).find('input').val($(valStreetCheckout).text());
						$(fieldStreetCheckout).find('input').keyup();

				    	$('input[name="postcode"]').val($(valStreetCheckout).attr('postalCode'));
				    	$('input[name="postcode"]').keyup();
				    });
				    
			    }
			});
	    }

	    if (window.location.href.indexOf("checkout") > -1) {
	    	var fieldStateCheckout;
	    	intervalState = setInterval(function(){
    			fieldStateCheckout = $('form .fieldset > .field[name="shippingAddress.region_id"] .control');
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
	        	$('footer .footer .footer-nav .second-nav').append(parentLi);
	      	}
	    });



	    // =============================================
	    // Sticky product nav
	    // =============================================
	    var stickyProductNav = $('.sticky-product-nav');

	    if($(stickyProductNav).length){
	    	$('.sticky-product-nav').remove();
	    	$('.page-wrapper .page-main>.columns').prepend(stickyProductNav);
	    }

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