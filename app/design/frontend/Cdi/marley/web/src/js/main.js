require([
    'jquery'
],
function ($, Component) {
  'use strict';

  var navbarHeight = jQuery('header').innerHeight();
  var w_width = jQuery( window ).width();

  $(document).ready(function(){

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
      var list = $(".products-grid .product-items .item");
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
    // Add * to inout required
    // =============================================

    var required = $('input.required-entry, select.required-entry');
    $.each(required, function(i, val){
      if(!$(val).parents('.field').hasClass('required')){
        $(val).parents('.field').addClass('required');
      }
    });



    // =============================================
    // Get cities
    // =============================================

    var fieldState = $('form .fieldset > .field.region #region_id');

    fieldState.on('change', function (e) {
      $.ajax({
        url: '/places/search/',
        data: 'parentId='+fieldState.find('option:selected').attr('parentId');,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
          console.log(res);
        }
      });      
    });


  });


  
  $(document).ajaxComplete(function(){

    // =============================================
    // Get states
    // =============================================

    var fieldState = $('form .fieldset > .field.region #region_id');
    var statusField;

    if(statusField==undefined){
      if($(fieldState).length){
        var stateOptions = $(fieldState).find('option');
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
            statusField=1;
          }
        });
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