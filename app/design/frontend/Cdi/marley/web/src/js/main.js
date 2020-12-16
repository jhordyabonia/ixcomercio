require(['jquery', 'jquery/ui', 'mage/translate', 'mainJs', 'domReady!'],
function ($, Component) {
  'use strict';

  var navbarHeight = jQuery('header').innerHeight();
  var w_width = jQuery( window ).width();
  var statusField;
  var totalItemsMinicart = $('.block-minicart .product-item');

  $(document).ready(function(){

    totalItemsMinicart=totalItemsMinicart.length;

    if(w_width <= 959){
      $('html body').css('paddingTop', navbarHeight);
    }else{
      $('html body').css('paddingTop', 0);
    }

    $('#scroll-to-top').click(function(){
      $( "html, body" ).animate({scrollTop:0}, 500, 'swing');
    });


    //Append search mobile
    if(w_width <= 768){
      var searchMobile = $('#search-wrapper-mobile .block-search');
      var minicartMobile = $('#mini-cart-wrapper-mobile .minicart-wrapper');

      if($(searchMobile).length == 0){
        $('#search-wrapper-mobile').append($('.block-search'));
      }
      if($(minicartMobile).length == 0){
        $('#mini-cart-wrapper-mobile').append($('.minicart-wrapper'));
      }
    }else{
      $(searchMobile).remove();
      $(minicartMobile).remove();
    }


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
    

    //Menu mobile
    $("header.header-primary-container .mobnav-trigger-wrapper .mobnav-trigger").click(function(){
      jQuery("header.header-primary-container .wrapper-nav").css("top", navbarHeight+"px");

      if($(this).hasClass("open")){
        $(this).removeClass("open");
        $("header.header-primary-container .wrapper-nav").slideUp();
      }else{
        $(this).addClass("open");
        $("header.header-primary-container .wrapper-nav").slideDown();
      }
    });



    // =============================================
    // Footer Mobile
    // =============================================

    $("footer .footer-primary .row>div h3").click(function(){
      if($(this).hasClass("open")){
        $(this).removeClass("open");
        $(this).parent().find("ul").slideUp();
      }else{
        $("footer .footer-primary .row > div h3").removeClass("open");
        $("footer .footer-primary .row > div ul").slideUp();
        $(this).addClass("open");
        $(this).parent().find("ul").slideDown();
      }
    });

    //Title Hero banner mobile
    if(jQuery(".hero-banner")){
      var textHeroBanner = jQuery(".hero-banner .text-overlay-banner");
      jQuery(".hero-banner .text-block-banner").prepend(textHeroBanner);
      jQuery(textHeroBanner).addClass("show");
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
    // Mobile Skip Links
    // =============================================

    var skipContents = $('.skip-content');
    var skipLinks = $('.skip-link');
    
    skipLinks.on('click', function (e) {
      e.preventDefault();
      var self = $(this);
      var target = self.attr('href');
      //Get target element
      var elem = $(target);
      //Check if stub is open
      var isSkipContentOpen = elem.hasClass('skip-active') ? 1 : 0;
      //Hide all stubs
      skipLinks.removeClass('skip-active');
      skipContents.removeClass('skip-active');
      //Toggle stubs
      if (isSkipContentOpen) {
        self.removeClass('skip-active');
      }else{
        self.addClass('skip-active');
        elem.addClass('skip-active');
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
                  html += "<option value='"+valRes.name+"' parentid='"+valRes.traxId+"''>"+valRes.name+"</option>";
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




  var countAjaxComplete = 0;
  
  
  $(document).ajaxComplete(function(){
    if($('.totals.sub .price').text() != ""){
      countAjaxComplete += 1;
      if(countAjaxComplete == 1){
        updateShoppingCart();
      }
    }


    function updateShoppingCart(){
      var itemsCart = $('.cart.table-wrapper .items>.item .col.subtotal .price');
      var subtotal = 0;
      $.each(itemsCart, function(i, val){
        var item = $(val).text().split(/\s+/);
        $.each(item, function(iItem, valItem){
          var price = (valItem).replace(/\./g, '');
            price = (price).replace(/\,/g, '.');
          if($.isNumeric(parseFloat(price)) == true){
            subtotal += parseFloat(price);
          }
        });
      });
      console.log(subtotal);
      console.log(Number(subtotal).toFixed(2));


      var orderSubtotal = $('.totals.sub .price').text().split(/\s+/);
      var summarySubtotal = 0;
      $.each(orderSubtotal, function(i, val){
          var summaryPriceSubtotal = (val).replace(/\./g, '');
            summaryPriceSubtotal = (summaryPriceSubtotal).replace(/\,/g, '.');
          if($.isNumeric(summaryPriceSubtotal) == true){
              summarySubtotal += parseFloat(summaryPriceSubtotal);
          }
      });
      console.log(summarySubtotal);
      console.log(Number(summarySubtotal).toFixed(2));


      var orderTax = $('.totals-tax-summary .price').text().split(/\s+/);
      var summaryTax = 0;
      $.each(orderTax, function(i, val){
      var summaryPriceTax = (val).replace(/\./g, '');
            summaryPriceTax = (summaryPriceTax).replace(/\,/g, '.');
          if($.isNumeric(summaryPriceTax) == true){
              summaryTax += parseFloat(summaryPriceTax);
          }
      });
      console.log(summaryTax);
      console.log(Number(summaryTax).toFixed(2));
      console.log(Number(summarySubtotal+summaryTax).toFixed(2));


      if(Number(subtotal).toFixed(2) != Number(summarySubtotal+summaryTax).toFixed(2)){
        console.log("dif");
        $('.cart-container form.form-cart .cart.main.actions .action.update').trigger('click'); 
      }else{
        console.log("similar");
      }
    }
  });


  
  var windowScroll_t;
  
  jQuery(window).on("scroll", function(){
    clearTimeout(windowScroll_t);
    windowScroll_t = setTimeout(function() {
      if ($(this).scrollTop() > 100){
        $('#scroll-to-top').fadeIn();
      }else{
        $('#scroll-to-top').fadeOut();
      }
    }, 500);
  });



  jQuery(window).on("resize", function(){
    navbarHeight = jQuery('header').innerHeight();
    if(w_width <= 959){
      $('html body').css('paddingTop', navbarHeight);
    }else{
      $('html body').css('paddingTop', 0);
    }

    //Append search mobile
    if(w_width <= 768){
      var searchMobile = $('#search-wrapper-mobile .block-search');
      var minicartMobile = $('#mini-cart-wrapper-mobile .minicart-wrapper');

      if($(searchMobile).length == 0){
        $('#search-wrapper-mobile').append($('.block-search'));
      }
      if($(minicartMobile).length == 0){
        $('#mini-cart-wrapper-mobile').append($('.minicart-wrapper'));
      }
    }else{
      $(searchMobile).remove();
      $(minicartMobile).remove();
    }



  });


  return Component;
});