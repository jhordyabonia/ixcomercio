define([
    'jquery',
], function ($) {
    'use strict';

    return function (Form) {
        return Form.extend({
            initialize: function () {
                this._super();
                //this.updateMenu();

            },

            updateMenu: function () {
                console.log("method custom");

                var menu = $("#payment_methods_menu").find('ul');

                var title_cont = $(".payment-method-title.custom_method");

                var title = $(title_cont).find('label.label span').text();
                var input = $(title_cont).find('input');
                var code_payment = $(input).attr('id');


                $(menu).prepend('<li role="presentation" class="payment-group-item debitcard active"><a id="link-'+ code_payment+ '" data-code="'+ code_payment+ '">'+title+'</a></li>');

                $('#'+code_payment).trigger( "click" );

                $(document).on('click', `#payment_methods_menu ul li a#link-`+code_payment, function (event) {

                    var data = $(this).attr('data-code');

                    $('#'+data).trigger( "click" );

                    if($(this).parent().hasClass('active')){

                    }else{
                        $(menu).find('li.active').removeClass('active');
                        $(this).parent().addClass('active');
                    }

                });


            }
        });
    }
});