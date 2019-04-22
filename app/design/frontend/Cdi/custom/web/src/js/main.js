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

});

var windowScroll_t;
$(window).scroll(function(){
	clearTimeout(windowScroll_t);
	windowScroll_t = setTimeout(function() {
		if ($(this).scrollTop() > 100){
			$('#scroll-to-top').addClass("show");
		}else{
			$('#scroll-to-top').removeClass("show");
		}
	}, 500);
});
$('#scroll-to-top').click(function(){
	$( "html, body" ).animate({scrollTop:0}, 500, 'swing');
});
