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
        'Intcomex_Credomatic/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'mage/url',

    ],
    function (ko,Component, $,setPaymentMethodAction,quote,storage,url) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Intcomex_Credomatic/payment/stripe-form',
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
                
                var serviceUrl = url.build('credomatic/custom/getorder');  
                var urlPostOrder = url.build('credomatic/custom/postorder');  
                var urlGetResponse = url.build('credomatic/custom/getresponse');  
                var urlPaymentResponse = url.build('credomatic/custom/paymentresponse');  
                var cuotas = $("#credomatic_installments option:selected").val();
                var year = $("#credomatic_expiration_yr option:selected").val();
                var month = $("#credomatic_expiration option:selected").val();
                var number = $("#credomatic_cc_number").val();
                var cvv_ = $("#credomatic_cc_cid").val();
                $.post(serviceUrl,{cart_id:quote.getQuoteId(),cuotas:cuotas,year:year,month:month,number:number,cvv_:cvv_})
                .done(function(msg){ 
                   var data = JSON.parse(JSON.stringify(msg));
                    var serviceUrlPostOrder = urlPostOrder+'?'+data['info'];
                    $("#frame_Credomatic").attr("src", serviceUrlPostOrder);  
                    (function theLoop (i) {
                        setTimeout(function () {
                            console.log('Buscando ...'+i);
                            $.post(urlGetResponse,{order_id:data['orderid']})
                            .done(function(resp){
                                if(resp.status=='success'){
                                    console.log('Encontrado!');
                                    var redirectUrl = urlPaymentResponse+'?'+resp.info;
                                    console.log('redirectUrl')
                                    console.log(redirectUrl)
                                    window.location.href = redirectUrl;
                                    i=0;
                                    return false;
                                }
                             })
                            .fail(function(resp){
                                console.log(resp);
                             });
                             if(i==1){
                                var redirectUrl = urlPaymentResponse+'?orderid='+data['orderid']+'&empty=true';
                                window.location.href = redirectUrl;
                                i=0;
                                return false;
                             }
                            if (--i) {          // If i > 0, keep going
                            theLoop(i);       // Call the loop again, and pass it the current value of i
                            }
                        }, 9000);
                    })(5); 
                    
                })
                .fail(function(msg){

                })             

                return false;
            }
	                
            
        });
    }
);
