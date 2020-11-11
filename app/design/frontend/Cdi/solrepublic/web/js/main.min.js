require(['jquery', 'jquery/ui', 'mage/translate', 'mainJs', 'domReady!'],
function ($, Component) {
    'use strict';

    // Scroll down variables
    var didScroll;
    var lastScrollTop = 0;
    var delta = 5;
    var navbarHeight = jQuery('.page-header').outerHeight();

    $(document).ready(function(){
    	$('.page-wrapper .page-main').css('paddingTop', navbarHeight);

		$('#scroll-to-top').click(function(){
			$("html, body").animate({scrollTop: 0}, 600, "easeOutCubic");
			return false;
		});


		$(".gotoscroll .banner-button").click(function(e){
			e.preventDefault();
			var div = jQuery(this).closest('section.banner');
			var classList = div.attr('class').split(/\s+/);
			var query = '';
			$.each(classList, function(index, item){
				if(item.match("^class-")){
					query = item.replace("class-", '.');
					
				}else if(item.match("^id-")){
					query = item.replace("id-", '#');
				}
				if(query != ''){
					jQuery('html,body').animate({
						scrollTop: jQuery(query).offset().top - jQuery('header.page-header').outerHeight()
					}, 'slow');
				}
			});
		});


		jQuery(".SOLburger").on('click', function(){
			menuToggle();
			jQuery(".SOLburger").toggleClass("is-clicked");
		});


		function menuToggle(){
			if(jQuery('.SOLburger').hasClass("is-clicked")){
				jQuery('#sideMenu').animate({right: '-100%'}, 500);
			}else{
				jQuery('#sideMenu').show();
				jQuery('#sideMenu').animate({right:0}, 500);
			}
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
		

		// Hide Header on on scroll down
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
			
			var countryCode = $('[name="country_id"]').val();

	    	$.ajax({
			    url: window.urlGeo+'set-regions?countryCode='+countryCode,
			    type: 'GET',
			    dataType: 'json',
			    success: function(res) {

					var response = JSON.parse(res);

			    	if($(fieldStateCheckout).find('input').length){
			    		$(fieldStateCheckout).find('input').hide();

			    		var html = '<select id="fieldStateCheckout" class="select" name="state_id" aria-required="true" aria-invalid="false">'+
	    					'<option data-title="" value="">'+$.mage.__("Please select a region, state or province.")+'</option>';

				        $.each(response.regions, function(iRes, valRes){
				        	html += "<option value='' parentid='"+valRes.traxId+"''>"+valRes.name+"</option>";
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
				            $.each(response.regions, function(iRes, valRes){
				            	if(valRes.name == optionName){
				                	$(valOpt).attr("parentId", valRes.traxId);
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
							url: window.urlGeo+'set-cities',
							data: 'parentId='+$(selectStateCheckout).find('option:selected').attr('parentId'),
							type: 'GET',
							dataType: 'json',
							success: function(resState) {

								var response = JSON.parse(resState);

								$(fieldCityCheckout).find('select').attr("disabled", false);
								$(fieldCityCheckout).find('select option:not([value=""])').remove();
								$.each(response.cities, function(iState, valState){
								    $(fieldCityCheckout).find('select').append("<option value='"+valState.traxId+"' parentId='"+valState.traxId+"'>"+valState.name+"</option>");
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
							url: window.urlGeo+'set-zones',
							data: 'parentId='+$(this).find('option:selected').attr('parentId'),
							type: 'GET',
							dataType: 'json',
							success: function(resCity) {

								var response = JSON.parse(resCity);

								$(fieldZoneCheckout).find('select').attr("disabled", false);
								$(fieldZoneCheckout).find('select option').remove();
                  				$(fieldZoneCheckout).find('select').append('<option data-title="" value="" selected>'+$.mage.__("Please select a zone.")+'</option>');
                  				
								$.each(response.localitaties, function(iResCity, valResCity){
							    	$(fieldZoneCheckout).find('select').append("<option value='"+valResCity.postalCode+"' parentId='"+valResCity.parentId+"' postalCode='"+valResCity.postalCode+"'>"+valResCity.name+"</option>");
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

	    
	});


	jQuery(window).scroll(function(event){
        didScroll = true;
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

	return Component;
});
