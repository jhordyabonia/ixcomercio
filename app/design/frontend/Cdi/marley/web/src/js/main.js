require([
    'jquery'
],
function ($, Component) {
  'use strict';

  $(document).ready(function(){
    
    $('#scroll-to-top').click(function(){
      $( "html, body" ).animate({scrollTop:0}, 500, 'swing');
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