require([
    'jquery'
],
function ($, Component) {
  'use strict';

  var navbarHeight = jQuery('header').outerHeight();

  $(document).ready(function(){

    $('#scroll-to-top').click(function(){
      $( "html, body" ).animate({scrollTop:0}, 500, 'swing');
    });


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
      if($(this).hasClass("open")){
        $(this).removeClass("open");
        $("header.header-primary-container .wrapper-nav").slideUp();
      }else{
        $(this).addClass("open");
        $("header.header-primary-container .wrapper-nav").slideDown();
      }
    });

    //Footer mobile
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

  });


  
  $(document).ajaxComplete(function(){
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


  return Component;
});