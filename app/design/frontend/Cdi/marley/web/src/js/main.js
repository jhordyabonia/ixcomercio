require([
    'jquery'
],
function ($, Component) {
  'use strict';

  $(document).ready(function(){
    
    $('#scroll-to-top').click(function(){
      $( "html, body" ).animate({scrollTop:0}, 500, 'swing');
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

    //Accordeon mobile
    $(".product-tabs .tab-content .tab-pane .card-title").click(function(){
      if($(this).parent().hasClass("active")){
        $(this).parent().removeClass("show active");
      }else{
        $(".product-tabs .tab-content .tab-pane").removeClass("show active");
        $(this).parent().addClass("show active");
      }
    });

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