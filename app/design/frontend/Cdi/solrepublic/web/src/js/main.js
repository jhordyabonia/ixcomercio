require([
    'jquery'
],
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
	    // Get states
	    // =============================================

	    var fieldState = $('form .fieldset > .field.region #region_id');
	    var stateOptions;
	    var intervalState;

	    function getStates(){
	    	$('body').trigger('processStart');
	    	$('#zip').val('');
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

	    fieldState.on('change', function (e) {
	    	$('body').trigger('processStart');
	    	$('#zip').val('');
	      	$.ajax({
		        url: '/places/search/',
		        data: 'parentId='+fieldState.find('option:selected').attr('parentid'),
		        type: 'GET',
		        dataType: 'json',
		        success: function(res) {
			        $(fieldCity).find('option:not([value=""])').remove();
			        $.each(res, function(i, val){
			            $(fieldCity).append("<option value='"+val.Id+"' parentid='"+val.Id+"'>"+val.Name+"</option>");
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
	    	$('#zip').val('');
	    	$.ajax({
				url: '/places/search/',
				data: 'parentId='+$('#city_id').find('option:selected').attr('parentid'),
				type: 'GET',
				dataType: 'json',
				success: function(resCity) {
					$(fieldZoneStreet).find('select option:not([value=""])').remove();
					
					$.each(resCity, function(iResCity, valResCity){
				    	$(fieldZoneStreet).find('select').append("<option value='"+valResCity.ParentId+"' parentid='"+valResCity.ParentId+"' postalcode='"+valResCity.PostalCode+"'>"+valResCity.Name+"</option>");
				  	});
					
				  	$('body').trigger('processStop');

				}
			});

			var valCity = $('#city_id').find('option:selected');
			$('#city_id').parent().find('input').val($(valCity).text());
			$('#city_id').parent().find('input').keyup();
	    });


	    // =============================================
	    // Print postal code
	    // =============================================

	    fieldZoneStreet.on('change', function (e) {
	    	$('body').trigger('processStart');
	    	var valStreet = $(fieldZoneStreet).find('option:selected');

	    	if($(valStreet).attr('postalcode') != 'null' && $('#zip').hasClass('required-entry')){
				$('#zip').val($(valStreet).attr('postalcode'));
	    		$('#zip').keyup();	
			}

	    	$('body').trigger('processStop');
	    });




	    // =============================================
	    // Print select Address checkout
	    // =============================================
	    var fieldStateCheckout;
	    var fieldCityCheckout;
	    function getStatesCheckout(obj){
	    	$('body').trigger('processStart');

	    	if($(obj).find("select[name='country_id']").val() == "GT"){
	    		$(obj).find('input[name="postcode"]').parents('.field').hide();
	    	}else{
	    		$(obj).find('input[name="postcode"]').parents('.field').show();
	    	}
	    	$(obj).find('input[name="postcode"]').val('');

	    	var fieldStreetCheckout = $(obj).find('> .field.street .control .additional .control');
	    	var fieldZoneCheckout = $(obj).find('> .field input[name="custom_attributes[zone_id]"]').parent();
		    
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
			    	var htmlCities = '<select id="fieldCityCheckout" class="select" name="cities_id" aria-required="true" aria-invalid="false" disabled>'+
	    							'<option data-title="" value="">Please select a city.</option>'+
	    							'</select>';
	    			$(fieldCityCheckout).append(htmlCities);

	    			var htmlZones = '<select id="fieldZoneCheckout" class="select" name="zone_id" aria-required="true" aria-invalid="false" disabled>'+
	    							'<option data-title="" value="">Please select a zone.</option>'+
	    							'</select>';
	    			$(fieldZoneCheckout).append(htmlZones);

	    			$('body').trigger('processStop');

	    			// =============================================
				    // Print select City checkout
				    // =============================================
				    var selectStateCheckout = $(fieldStateCheckout).find('select');
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
				    $(obj).find('#fieldCityCheckout').on('change', function (e) {
				    	$('body').trigger('processStart');
				    	$(obj).find('input[name="postcode"]').val('');
						var valCity = $(fieldCityCheckout).find('select option:selected');
						$(fieldCityCheckout).find('input').val($(valCity).text());
						$(fieldCityCheckout).find('input').keyup();

						$.ajax({
							url: '/places/search/',
							data: 'parentId='+$(this).find('option:selected').attr('parentId'),
							type: 'GET',
							dataType: 'json',
							success: function(resCity) {
								$(fieldZoneCheckout).find('select').attr("disabled", false);
								$(fieldZoneCheckout).find('select option').remove();
                  				$(fieldZoneCheckout).find('select').append('<option data-title="" value="" selected>Please select a zone.</option>');
                  				
								$.each(resCity, function(iResCity, valResCity){
							    	$(fieldZoneCheckout).find('select').append("<option value='"+valResCity.ParentId+"' parentId='"+valResCity.ParentId+"' postalCode='"+valResCity.PostalCode+"'>"+valResCity.Name+"</option>");
							  	});
							  	
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

				    	$(fieldStreetCheckout).find('input').val($(valStreetCheckout).text());
						$(fieldStreetCheckout).find('input').keyup();

				    	if($(valStreetCheckout).attr('postalCode') != 'null'){
							$('input[name="postcode"]').val($(valStreetCheckout).attr('postalCode'));
				    		$('input[name="postcode"]').keyup();
						}else{
							$('input[name="postcode"]').val($(valStreetCheckout).text());
				    		$('input[name="postcode"]').keyup();
						}

				    	$('body').trigger('processStop');
				    });
			    }
			});
	    }

	    if (window.location.href.indexOf("checkout") > -1) {
	    	intervalState = setInterval(function(){
    			fieldStateCheckout = $('form .fieldset.address input[name="region"]').parent();
    			if($(fieldStateCheckout).length >= 1){
		        	getStatesCheckout('form .fieldset.address');
		          	clearInterval(intervalState);
		        }
	      	}, 1000);
	    }

	    var flagBillingForm = 0;
	    $(document).on('change',"[name='billing-address-same-as-shipping']",function(){
	    	if($('.field-select-billing select').length == 0){
	    		flagBillingForm += 1;
		        if($(this).prop('checked') == false){
			        fieldStateCheckout = $('.billing-address-form form fieldset.address input[name="region"]').parent();
			        if($(fieldStateCheckout).length >= 1 && flagBillingForm == 1){
	                	getStatesCheckout($('.billing-address-form form fieldset.address'));
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
	        	getStatesCheckout($('.billing-address-form form fieldset.address'));
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
	    // Zendesk link - footer
	    // =============================================
	    var linksFooter = $('footer a');

	    $.each(linksFooter, function(i, val){
	    	if(val.innerText == "Zendesk Support" || val.innerText == "Preguntas frecuentes"){
	        	var parentLi = $(this).parent();
	        	$('footer .footer-resources ul').append(parentLi);
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
