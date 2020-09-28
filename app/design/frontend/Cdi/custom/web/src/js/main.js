require(['jquery', 'jquery/ui', 'mage/translate', 'mainJs', 'domReady!'],
function ($, Component) {
    'use strict';
    
	jQuery('#scroll-to-top').click(function(){
		jQuery("html, body").animate({scrollTop: 0}, 600, "easeOutCubic");
		return false;
	});

	var w_width = jQuery( window ).width();

	$(document).ready(function(){

		// =============================================
	    // Add * to input required
	    // =============================================

	    var required = $('input.required-entry, select.required-entry');
	    $.each(required, function(i, val){
	      if(!$(val).parents('.field').hasClass('required')){
	        $(val).parents('.field').addClass('required');
	      }
	    });


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
	    // Data Edit Address
	    // =============================================
		function setValueWsElement(elementSrc, elementDest, type){
			if($(elementSrc).length && elementDest.length){
				var valSrc = $(elementSrc).val();
				var options = $(elementDest).find('option');
				if(options.length){
					if(type == 'text'){
						$.each(options, function(iO, valO){
							if($(valO).text() == valSrc){
								$(valO).prop('selected', true);
								setTimeout(function(){
									$(elementDest).trigger('change');
								},500);
							}
						});
					}else if(type == 'val'){
						$.each(options, function(iO, valO){
                            if($(valO).attr('value') == valSrc){
								$(valO).prop('selected', true);
								setTimeout(function(){
									$(elementDest).trigger('change');
								},500);
							}
						});
					}
				}else{
					console.log('el ws no retornó opciones para ' + $(elementDest).attr('id'));
				}
			}
		}



	    // =============================================
	    // Print select Address checkout
	    // =============================================
	    var intervalState;
	    var fieldStateCheckout;
	    var fieldCityCheckout;
	    function getStatesCheckout(obj, zone){
	    	$('body').trigger('processStart');

	    	if($('[name="country_id"]').val() == "GT"){
	    		$(obj).find('input[name="postcode"]').parents('.field').hide();
	    	}else{
	    		$(obj).find('input[name="postcode"]').parents('.field').show();
	    	}
	    	
	    	$(obj).find('input[name="postcode"]').val('');

	    	var fieldStreetCheckout = $(obj).find('> .field.street .control .additional .control');
	    	var fieldZoneCheckout = $(obj).find(zone).parent();

		    $(fieldStreetCheckout).find('input').hide();
		    $(fieldZoneCheckout).find('input').hide();

	    	fieldCityCheckout = $(obj).find('> .field input[name="city"]').parent();

	    	$.ajax({
			    url: '/places/search/',
			    type: 'GET',
			    dataType: 'json',
			    success: function(res) {
			    	if($(fieldStateCheckout).find('input').length){
			    		$(fieldStateCheckout).find('input').hide();

			    		var html = '<select id="fieldStateCheckout" class="select" name="state_id" aria-required="true" aria-invalid="false">'+
	    					'<option data-title="" value="">'+$.mage.__("Please select a region, state or province.")+'</option>';

				        $.each(res, function(iRes, valRes){
				        	html += "<option value='' parentid='"+valRes.Id+"''>"+valRes.Name+"</option>";
				        });

				        html += '</select>';

				        if($(obj).find('#fieldStateCheckout').length == 0){
				        	$(fieldStateCheckout).append(html);	
				        }

						setValueWsElement($(obj).find('#fieldStateCheckout').parent().find('input'), $(obj).find('#fieldStateCheckout'), 'text');
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
			    	var htmlCities = '<select id="fieldCityCheckout" class="select" name="cities_id" aria-required="true" aria-invalid="false" disabled>'+
	    							'<option data-title="" value="">'+$.mage.__("Please select a city.")+'</option>'+
	    							'</select>';

	    			if($(obj).find('#fieldCityCheckout').length == 0){
	    				$(fieldCityCheckout).append(htmlCities);
	    			}

	    			var htmlZones = '<select id="fieldZoneCheckout" class="select" name="zone_id" aria-required="true" aria-invalid="false" disabled>'+
	    							'<option data-title="" value="">'+$.mage.__("Please select a zone.")+'</option>'+
	    							'</select>';

	    			if($(obj).find('#fieldZoneCheckout').length == 0){
	    				$(fieldZoneCheckout).append(htmlZones);
	    			}

	    			$('body').trigger('processStop');

	    			// =============================================
				    // Print select City checkout
				    // =============================================
				    var selectStateCheckout = $(fieldStateCheckout).find('select#fieldStateCheckout');
				    $(selectStateCheckout).on('change', function (e) {
				    	$('body').trigger('processStart');
				    	$(obj).find('input[name="postcode"]').val('');
				    	
				    	$.ajax({
							url: '/places/search/',
							data: 'parentId='+$(selectStateCheckout).find('option:selected').attr('parentId'),
							type: 'GET',
							dataType: 'json',
							success: function(resState) {
								$(fieldCityCheckout).find('select').attr("disabled", false);
								$(fieldCityCheckout).find('select option:not([value=""])').remove();
								$.each(resState, function(iState, valState){
								    $(fieldCityCheckout).find('select').append("<option value='"+valState.Id+"' parentId='"+valState.Id+"'>"+valState.Name+"</option>");
								});

								setValueWsElement('#city', '#fieldCityCheckout', 'text');

								$('body').trigger('processStop');
							}
						});

						$(fieldStateCheckout).find('input').val($(this).find('option:selected').text());
						$(fieldStateCheckout).find('input').keyup();
					    
				    });
				    
				    // =============================================
				    // Print select street checkout
				    // =============================================
				    $(obj).find('#fieldCityCheckout').on('change', function (e) {
				    	$('body').trigger('processStart');
				    	$(obj).find('input[name="postcode"]').val('');
						var valCity = $(fieldCityCheckout).find('select option:selected');
						$(fieldCityCheckout).find('input').val($(this).find('option:selected').text());
						$(fieldCityCheckout).find('input').keyup();

						$.ajax({
							url: '/places/search/',
							data: 'parentId='+$(this).find('option:selected').attr('parentId'),
							type: 'GET',
							dataType: 'json',
							success: function(resCity) {
								$(fieldZoneCheckout).find('select').attr("disabled", false);
								$(fieldZoneCheckout).find('select option').remove();
                  				$(fieldZoneCheckout).find('select').append('<option data-title="" value="" selected>'+$.mage.__("Please select a zone.")+'</option>');
                  				
								$.each(resCity, function(iResCity, valResCity){
							    	$(fieldZoneCheckout).find('select').append("<option value='"+valResCity.PostalCode+"' parentId='"+valResCity.ParentId+"' postalCode='"+valResCity.PostalCode+"'>"+valResCity.Name+"</option>");
							  	});

								setValueWsElement('#zone_id', '#fieldZoneCheckout', 'val');
							  	
							  	$('body').trigger('processStop');
							}
						});
				    });
				    
				    // =============================================
				    // Print postal code
				    // =============================================
				    $(fieldZoneCheckout).find('select').on('change', function (e) {
				    	$('body').trigger('processStart');
				    	var valStreetCheckout = $(fieldZoneCheckout).find('select option:selected');
				    	
				    	$(fieldZoneCheckout).find('input').val($(this).find('option:selected').text());
				    	$(fieldZoneCheckout).find('input').keyup();

				    	$(fieldStreetCheckout).find('input').val($(this).find('option:selected').text());
						$(fieldStreetCheckout).find('input').keyup();

				    	if($(valStreetCheckout).attr('postalCode') != 'null'){
				    		$(obj).find('input[name="postcode"]').show();
							$(obj).find('input[name="postcode"]').val($(valStreetCheckout).attr('postalCode'));
				    		$(obj).find('input[name="postcode"]').keyup();
						}else{
							$(obj).find('input[name="postcode"]').hide();
							$(obj).find('input[name="postcode"]').val($(valStreetCheckout).text());
				    		$(obj).find('input[name="postcode"]').keyup();
						}

				    	$('body').trigger('processStop');
				    });
			    }
			});
	    }


	    if ($('body').hasClass('checkout-index-index')) {
	    	intervalState = setInterval(function(){
    			fieldStateCheckout = $('form .fieldset.address input[name="region"]').parent();
    			if($(fieldStateCheckout).length >= 1){
		        	getStatesCheckout('form .fieldset.address', '> .field input[name="custom_attributes[zone_id]"]');
		          	clearInterval(intervalState);
		        }
	      	}, 1000);
	    }


	    if ($('body').hasClass('checkout-cart-index')) {
            intervalState = setInterval(function(){
                fieldStateCheckout = $('form .fieldset.estimate input[name="region"]').parent();
                if($(fieldStateCheckout).length >= 1){
                    getStatesCheckout('form .fieldset.estimate', '> .field input[name="custom_attributes[zone_id]"]');
		          	clearInterval(intervalState);
		        }
	      	}, 1000);
	    }


	    if (window.location.href.indexOf("customer") > -1) {
	    	intervalState = setInterval(function(){
	    		fieldStateCheckout = $('form.form-address-edit .fieldset input[name="region"]').parent();
    			if($(fieldStateCheckout).length >= 1){
    				$('.form-address-edit .field-name-firstname').before($('.field-identification'));
    				$('.form-address-edit .field-zone_id').after($('.field.zip'));
    				getStatesCheckout('form.form-address-edit .fieldset', '.field input[name="zone_id"]');
		          	clearInterval(intervalState);
		        }
	      	}, 1000);
	    }


	    var flagBillingForm = 0;
	    $(document).on('change',"[name='billing-address-same-as-shipping']",function(){
	    	if($('.field-select-billing select').length == 0){
	    		flagBillingForm += 1;
		        if($(this).prop('checked') == false){
		        	var parentForm = $('.payment-method._active .billing-address-form form fieldset.address');
		        	fieldStateCheckout = $(parentForm).find('input[name="region"]').parent();
		        	console.log('fieldStateCheckout '+fieldStateCheckout);
			        if($(fieldStateCheckout).length >= 1 && flagBillingForm == 1){
			        	getStatesCheckout(parentForm, '> .field input[name="custom_attributes[zone_id]"]');
			    	}
			    }
	    	}else {
	    		flagBillingForm = 0;
	    	}
	    });

	    $(document).on('change',"[name='billing_address_id']",function(){
	    	flagBillingForm += 1;
	    	fieldStateCheckout = $('.billing-address-form form fieldset.address input[name="region"]').parent();
	        if(flagBillingForm <= 1){
	        	getStatesCheckout($('.billing-address-form form fieldset.address'), '> .field input[name="custom_attributes[zone_id]"]');
	        }
	    });

	    $(document).on('change',"[name='country_id']",function(){
	    	if($(this).val() == "GT"){
	    		$('input[name="postcode"]').parents('.field').hide();
	    	}else{
	    		$('input[name="postcode"]').parents('.field').show();
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
