//VALIDATE FORM
//FORM
var error = true;
function validate(obj){
  jQuery(".msg-error").remove();
  var inputs = jQuery(obj).find("input, select, textarea").not(':input[type=file],:input[type=button], :input[type=submit], :input[type=reset], .hidden');

  jQuery(".msg-error").remove();
  jQuery(inputs).removeClass("validation-failed");

  jQuery.each(inputs, function(i, val){
    var thisInput = jQuery(val).attr("id");
    thisInput = "#"+thisInput;
    if(jQuery(val).hasClass("required-entry")){
      if(jQuery(val).val() == ""){
        error = true;
        msgError(thisInput, "Este campo es obligatorio");
      }
      //TYPE EMAIL
      else if(jQuery(val).attr("type") == "email"){
        if(jQuery(val).val() != ""){
          var pattern = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
          var emailVal = jQuery.trim(jQuery(val).val()).match(pattern) ? true : false;
          if( emailVal == false){
            error = true;
            msgError(thisInput, "Ingrese una dirección de correo electrónico válida. Por ejemplo: juanperez@dominio.com.");
          }
        }
      }
      //NUMBERS
      else if(jQuery(val).attr("type") == "number"){
        var reg = /^\d+$/;
        var compare = String(jQuery(val).val());
        if(reg.test(compare)==false){
          error = true;
          msgError(thisInput, "Ingresa un dato numérico sin signos ni puntuaciones");
        }
      }
      //ONLY TEXT
      else if(jQuery(val).hasClass("only-text")){
        //var exp = /^[A-Za-z\s]+$/;
        var exp = /^[a-zA-ZÀ-ÿ\u00f1\u00d1]+(\s*[a-zA-ZÀ-ÿ\u00f1\u00d1]*)*[a-zA-ZÀ-ÿ\u00f1\u00d1]+$/g;
        var compare = String(jQuery(val).val());
        if(exp.test(compare)==false){
          error = true;
          msgError(thisInput, "Ingresa un dato sin números o caracteres especiales");
        }
      }
    }
  });

  if($(".validation-failed").length == 0){
    send();
    return false;
  }

}

function sendMail() {
    var link = "mailto:luis.maldonado@ariadnacg.com"
             + "?cc=lorena.castrillon@ariadnacg.com"
             + "&subject=" + escape("Mensaje de prueba - Marley")
             + "&body=" + escape("Nombre: "+$("#name").val()+"<br>Email: "+$("#email").val()+"<br>Teléfono: "+$("#telephone").val()+"<br>Comentario: "+$("#comment").val());

    window.location.href = link;
}

function send() {
    var link = 'mailto:email@example.com?subject=Message from '
             +document.getElementById('email').value
             +'&body='+document.getElementById('comment').value;
    window.location.href = link;
}

//MSG ERROR
function msgError(input, msg){
  jQuery(input).addClass("validation-failed");
  jQuery(input).parent().append("<div class='msg-error'><div class='arrow'></div><span>"+msg+"</span></div>");
}
function hideMsgError(obj){
  var haveMsg = jQuery(obj).parent().find(".msg-error");
  if(haveMsg.length !=0){
    jQuery(haveMsg).remove();
  }
}

function clearData(){
  jQuery(" form input").val("");
  jQuery("form textarea").val("");
  jQuery("form input[type='checkbox']").prop( "checked", false );
  jQuery("form select").val("0").trigger("change");
  error=true;
}



function getUrlParameter(sParam){
  var sPageURL = window.location.search.substring(1),
      sURLVariables = sPageURL.split('&'),
      sParameterName,
      i;

  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split('=');

    if (sParameterName[0] === sParam) {
      return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
    }
  }
};


function pagerList(){
  //Category page
  if($(".product-grid .pager").length){
    $("div.pager").jPages({
      containerID: "container-grid",
      perPage      : 24,
      previous    : "",
      next        : "",
      callback    : function( pages,
      items ){
        if(items.count <= 24){
          $(".product-grid .sorter .amount").html("<b>"+items.count + " artículo(s)</b>");
          $(".pager-wrapper").css("opacity", 0);
        }else{
          $(".product-grid .sorter .amount").html("Elementos "+items.range.start + " a " + items.range.end + " de un total de " + items.count);
          $(".pager-wrapper").css("opacity", 1);
        }
      }
    });
  }
}



var url = window.location.href;
var paramDir = "";
var paramOrder = "";
var listProducts;
var arrayList = [];
var hrefDir;

$(document).ready(function(){
  $(".section-main-slider .owl-carousel").owlCarousel({
    items:1,
    dots: false,
    loop: true,
    autoplay: true,
    smartSpeed:1000,
    mouseDrag: false,
    animateOut: 'fadeOut',
    autoplayHoverPause: false,
    autoplayTimeout: 8000,
    responsive : {
        0 : {
            nav:false
        },
        651 : {
            nav:true,
            navText: ["",""]
        }
    }
  });

  $(".product-view .more-images.owl-carousel").owlCarousel({
    dots: false,
    loop: false,
    autoplay: true,
    smartSpeed:1000,
    mouseDrag: false,
    nav:true,
    navText: ["",""],
    responsive : {
        0 : {
          items:3
        },
        1025 : {
          items:4
        }
    }
  });

  $(".landing-page .owl-carousel").owlCarousel({
    items:1,
    dots: false,
    loop: true,
    autoplay: true,
    smartSpeed:1500,
    mouseDrag: false,
    animateOut: 'fadeOut',
    autoplayHoverPause: false,
    autoplayTimeout: 9000,
    nav:false
  });

  $("#contactForm").submit(function(e){
    e.preventDefault(e);
    validate("#"+jQuery(this).attr("id"));
    });

  //Change item product slide
  $(".more-images.owl-carousel .owl-item img").click(function(){
    var img = $(this).attr("data-img");
    $(".product-view .product-img .wrapper-img img").attr("src", img);
    $(".product-view .product-img .wrapper-img #zoom-btn").attr("href", img);
    $("#cboxClose").trigger("click");
  });


  //DORT PRODUCT LIST
  if($(".product-grid").length){
    if(url.indexOf("?") != "-1" ){
      paramDir = getUrlParameter('dir');
      paramOrder = getUrlParameter('order');
      listProducts = $(".product-grid #container-grid li.item");
      hrefDir = paramDir;

      if(paramOrder == "name"){
        $.each(listProducts, function(i, val){
          var name = $(val).find(".product-name a").text();
          $(val).attr("data-order", name);
          arrayList.push(name);
        });
      }else if(paramOrder == "price"){
        var price;
        $.each(listProducts, function(i, val){
          price = $(val).find(".regular-price .price, .special-price .price").text();
          price = price.replace("$ ", "");
          $(val).attr("data-order", price);
        });
      }

      if(paramDir == "asc"){
        hrefDir="desc";
        $(".sort-by a").removeClass("category-desc ic ic-arrow-up");
        $(".sort-by a").addClass("category-desc ic ic-arrow-down");

        if(paramOrder == "price"){
          $("#container-grid .item").sort(sort_asc)
            .appendTo('#container-grid');
          
          function sort_asc(a, b){
            $("#container-grid");
              return ($(a).data('data-order')) > ($(b).data('data-order')) ? 1 : +1;    
          }
        }else if(paramOrder == "name"){
          arrayList.sort();
          $.each(arrayList, function(i, val){
            $.each(listProducts, function(iList, valList){
              if($(valList).attr("data-order") == val){
                var itemList = valList;
                $(valList).remove();
                $(".product-grid #container-grid").append(itemList);
              }
            });
          });
        }
        
      }else if(paramDir == "desc"){
        hrefDir="asc";
        $(".sort-by a").removeClass("category-desc ic ic-arrow-down");
        $(".sort-by a").addClass("category-desc ic ic-arrow-up");

        if(paramOrder == "price"){
          $("#container-grid .item").sort(sort_desc)
            .appendTo('#container-grid');
          
          function sort_desc(b, a){
            $("#container-grid");
              return ($(b).data('data-order')) < ($(a).data('data-order')) ? 1 : -1;    
          }
        }else if(paramOrder == "name"){

          arrayList.sort();
          arrayList.reverse();

          $.each(arrayList, function(i, val){
            $.each(listProducts, function(iList, valList){
              if($(valList).attr("data-order") == val){
                var itemList = valList;
                $(valList).remove();
                $(".product-grid #container-grid").append(itemList);
              }
            });
          });
        }
      }
    }

    if(paramOrder=="" && paramDir == ""){
      $(".sort-by a").attr("href", window.location.pathname+"?dir=desc&order=price");
    }else{
      $(".sort-by a").attr("href", window.location.pathname+"?dir="+hrefDir+"&order="+paramOrder);  
    }
    
    $(".sort-by select option:selected").prop("selected", false);
    $(".sort-by select option[value='"+paramOrder+"']").prop("selected", true);

    pagerList();

    $(".sort-by select").change(function(){
      if($(".sort-by select").val() != ""){
        window.location.href = window.location.origin+window.location.pathname+"?dir="+paramDir+"&order="+$(".sort-by select").val();
      }
    });
  }




  if($(".product-grid .sorter").length){
    /* on select change */
    $(".product-grid .sorter .limiter select").change(function(){
        /* get new nº of items per page */
      var newPerPage = parseInt( $(this).val() );
      /* destroy jPages and initiate plugin again */
      $("div.pager").jPages("destroy").jPages({
        containerID: "container-grid",
        perPage: newPerPage,
        previous    : "",
        next        : "",
        callback    : function( pages,
        items ){
          if(items.count <= 24){
            $(".product-grid .sorter .amount").html("<b>"+items.count + " artículo(s)</b>");
            $(".pager-wrapper").css("opacity", 0);
          }else{
            $(".product-grid .sorter .amount").html("Elementos "+items.range.start + " a " + items.range.end + " de un total de " + items.count);
            $(".pager-wrapper").css("opacity", 1);
          }
        }
      });
    });
  }


  if($(".product-view").length){
    $('.wrapper-img div').zoom();
  }

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

  //Zoom product detail
  if($(".lightbox-group").length){
    $zoomButton = $("#zoom-btn");
    $(".lightbox-group").colorbox({
      rel:'lightbox-group',
      opacity: 0.5,
      speed: 300,
      previous: '',
      next: '',
      close: '' //No comma here
      , maxWidth:'95%', maxHeight:'95%'
    });
  }
  

  //Product thumbnails - remove first one from gallery, it's already included in gallery by "zoom" button
  $(".cloud-zoom-gallery").first().removeClass("cboxElement");

  //Remove clicked thumbnail from gallery, it's already included in gallery by "zoom" button
  $(".cloud-zoom-gallery").click(function() {
    $zoomButton
    .attr('href', $(this).attr('href'))
    .attr('title', $(this).attr('title'));
    //First add gallery class to all thumbnails
    $(".cloud-zoom-gallery").each(function() {
      $(this).addClass("cboxElement");
    });
    $(this).removeClass("cboxElement");
  });

  //On image change
  $(document).on('product-media-manager-image-updated', function(e, data) {
    var img = data.img;
    //Add new image's URL to the zoom button's href attribute
    $zoomButton.attr('href', img.attr('src'));
    $zoomButton.attr('title', '');
    $(".cloud-zoom-gallery").each(function() {
      $(this).addClass("cboxElement");
    });
  }); //end: on event

});

var windowScroll_t;
$(window).scroll(function(){
  clearTimeout(windowScroll_t);
  windowScroll_t = setTimeout(function() {
    if ($(this).scrollTop() > 100){
      $('#scroll-to-top').fadeIn();
    }else{
      $('#scroll-to-top').fadeOut();
    }
  }, 500);
});
$('#scroll-to-top').click(function(){
  $( "html, body" ).animate({scrollTop:0}, 500, 'swing');
});
