var $ = jQuery;
$('input[name="billing-address-same-as-shipping"]').on('change', function(e){
		    if($(this).prop('checked') == false){
		        var fieldStateCheckout;
		        fieldStateCheckout = $('form fieldset[data-form="billing-new-address"] input[name="region"]').parent();
		        if($(fieldStateCheckout).length >= 1){
		            getStatesCheckout($('form fieldset[data-form="billing-new-address"]'));
		        }
		    }
		});


var fieldCityCheckout;
	    function getStatesCheckout(obj){

	    	$('body').trigger('processStart');
	    	$(obj).find('input[name="postcode"]').val('');
	    	var fieldStreetCheckout = $(obj).find('> .field.street .control .additional .control');
	    	var fieldZoneCheckout = $(obj).find('> .field select[name="custom_attributes[zone_id]"]');
	    	$(fieldZoneCheckout).attr("disabled", true);
		    
		    $(fieldStreetCheckout).find('input').hide();

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
								$(fieldZoneCheckout).attr("disabled", false);
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

				    $(obj).find('select[name="custom_attributes[zone_id]"]').on('change', function (e) {
				    	$('body').trigger('processStart');
				    	var valStreetCheckout = $(this).find('option:selected');
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