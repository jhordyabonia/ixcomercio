/**
 * Credomatic for Magento JS component
 *
 * @category    Credomatic
 * @package     Credomatic
 * @author      Intcomex
 * @copyright   Intcomex (https://www.intcomex.com/)
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'Intcomex_CredomaticVisa/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'mage/url',
        'https://www.jqueryscript.net/demo/MD5-Hash-String/jquery.md5.min.js'
    ],
    function (ko,Component, $,setPaymentMethodAction,quote,storage,url) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Intcomex_CredomaticVisa/payment/stripe-form',
            	  /*paymentPayload: {
                    nonce: null
                },
                creditCardInstallments: '',*/
	          },
	    
            getCode: function() {
                return 'credomatic';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },
 
            afterPlaceOrder: function () {
                jQuery('body').trigger('processStart');
                var serviceUrl = url.build('credomaticvisa/custom/getorder');  
                var urlPaymentResponse = url.build('credomaticvisa/custom/paymentresponse');  
                var cuotas = $("#credomaticvisa_installments option:selected").val();
                var year = $("#credomaticvisa_expiration_yr option:selected").val();
                var month = $("#credomaticvisa_expiration option:selected").val();
                var number = $("#credomaticvisa_cc_number").val();
                var cvv_ = $("#credomaticvisa_cc_cid").val();
                console.log(quote);
                $.post(serviceUrl,{cuotas:cuotas,year:year,month:month,number:number,cvv_:cvv_})
                .done(function(msg){
                    jQuery('body').trigger('processStart');
                    
                    let url = msg.url_gateway;
                    // Proceso post por medio de AJAX
                    let data = [];
                    data.push({name:'type',value:'sale'});
                    data.push({name:'key_id',value:msg.key_id});
                    data.push({name:'hash',value:msg.hash});
                    data.push({name:'time',value:msg.time});
                    data.push({name:'amount',value:msg.amount});
                    data.push({name:'orderid',value:msg.orderid});
                    data.push({name:'processor_id',value:msg.processor_id});
                    data.push({name:'firstname',value:msg.firstname});
                    data.push({name:'lastname',value:msg.lastname});
                    data.push({name:'email',value:msg.email});
                    data.push({name:'phone',value:msg.phone});
                    data.push({name:'street1',value:msg.address1});
                    data.push({name:'street2',value:msg.address2});
                    data.push({name:'cvv',value:cvv_});
                    data.push({name:'ccnumber',value:number});
                    data.push({name:'ccexp',value:msg.data3});
                    data.push({name:'redirect',value:msg.url_resp});
                    $.ajax({

                        url: url,
                        data: data,
                        type: 'POST',
                        crossDomain: true,
                        dataType: 'jsonp',
                        success: function() {
                        },
                        error: function() {
                        }
                    });
                    setTimeout(function(){
                        window.location.href = urlPaymentResponse; 
                    }, 1000);
                })
                .fail(function(msg){
                    window.location.href = urlPaymentResponse;
                })
                return false;
            },

            updateMenu: function () {
                let menu = $("#payment_methods_menu").find('ul');

                if (menu.length) {
                    let title_cont = $(".payment-method-title.credomatic");
                    let title = $(title_cont).find('label.label span').text();
                    let code_payment = $(title_cont).find('input').attr('id');

                    title_cont.hide();

                    $(menu).prepend(
                        '<li role="presentation" class="payment-group-item debitcard active">' +
                            '<a class="link_option_credomatic" style="padding-bottom: .5rem !important;" id="link-' + code_payment + '" data-code="' + code_payment + '">' + title + '</a>' +
                            '<img style="padding-left: 1rem; padding-right: 2.5rem; padding-bottom: 1rem;" src="'+window.imgFranquiciasBAC+'" >' +
                        '</li>'
                    );

                    $('#' + code_payment).trigger("click");

                    $(document).on('click', `#payment_methods_menu ul li a#link-` + code_payment, function (event) {
                        let data = $(this).attr('data-code');
                        $('#' + data).trigger("click");
                        if ($(this).parent().hasClass('active')) {

                        } else {
                            $(menu).find('li.active').removeClass('active');
                            $(this).parent().addClass('active');
                        }
                    });
                }
            }

        });
    }
);
