require([
    'jquery'
],
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
    
    fieldState.on('change', function (e) {
      $('body').trigger('processStart');
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

      $('#fieldSelectStreet').on('change', function (e) {
        $('body').trigger('processStart');
        var valStreet = $('#fieldSelectStreet').find('option:selected');
        
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
                //console.log(resState);
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
        $('footer .col-md-3:eq(0) .nav-submenu').append(parentLi);
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