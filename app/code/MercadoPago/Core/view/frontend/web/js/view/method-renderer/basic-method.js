define(
  [
    'Magento_Checkout/js/view/payment/default',
    'mage/translate',
    'jquery',
  ],
  function (Component, $t,$) {
    'use strict';

    let configPayment = window.checkoutConfig.payment.mercadopago_basic;
    return Component.extend({
      defaults: {
        template: 'MercadoPago_Core/payment/basic_method',
        paymentReady: false
      },
      redirectAfterPlaceOrder: false,

      initObservable: function () {
        this._super().observe('paymentReady');
        return this;
      },

      isPaymentReady: function () {
        return this.paymentReady();
      },

      afterPlaceOrder: function () {
        window.location = this.getActionUrl();
        console.log('this.getActionUrl()');
        console.log(this.getActionUrl());
      },

      placePendingPaymentOrder: function () {
        this.placeOrder();
      },
      initialize: function () {
        this._super();
        //this.updateMenu();
      },
      updateMenu: function () {
        console.log('test menu basic method');
        var menu = $("#payment_methods_menu").find('ul');

        var code_payment = this.getCode();

        if(window.currency=='COP'){
          var img1 = '';
          var img2 = '<img style="width: 35px; display: block;" src="'+window.pse+'" >';
        }else if(window.currency=='CLP'){
          var img1 = '<img style="width: 60px; display: inline-block;" src="'+window.webpay+'" >';
          var img2 = '<img style=" display: block;" src="'+window.franquicias2+'" >';
        }

        $(menu).append('<li role="presentation" class="payment-group-item basic"><a id="link-'+ code_payment+ '" data-code="'+ code_payment+ '">'+this.getTitle()+''+img1+''+img2+'</a></li>');


        jQuery(document).on('click', `#payment_methods_menu ul li a#link-`+code_payment, function (event) {
            var data = $(this).attr('data-code');

            $('#'+data).trigger( "click" );

            if($(this).parent().hasClass('active')){

            }else{
                $(menu).find('li.active').removeClass('active');
                $(this).parent().addClass('active');
            }

        });


    },

      getCode: function () {
        return 'mercadopago_basic';
      },

      getLogoUrl: function () {
        if (configPayment != null) {
          return configPayment['logoUrl'];
        }
        return '';
      },

      existBanner: function () {
        if (configPayment != null) {
          if (configPayment['bannerUrl'] != null) {
            return true;
          }
        }
        return false;
      },

      getBannerUrl: function () {
        if (configPayment != null) {
          return configPayment['bannerUrl'];
        }
        return '';
      },

      getActionUrl: function () {
        if (configPayment != null) {
          return configPayment['actionUrl'];
        }
        return '';
      },

      getRedirectImage: function () {
        return configPayment['redirect_image'];
      },

      getInfoBanner: function ($pm) {
        if (configPayment != null && configPayment['banner_info'] != null) {
          return configPayment['banner_info'][$pm];
        }
        return 0;
      },

      getInfoBannerInstallments: function () {
        if (configPayment != null && configPayment['banner_info'] != null) {
          return configPayment['banner_info']['installments'];
        }
        return 0;
      },

      getInfoBannerPaymentMethods: function ($pmFilter) {
        var listPm = []

        if (configPayment != null && configPayment['banner_info'] != null) {
          var paymetMethods = configPayment['banner_info']['checkout_methods'];
          if (paymetMethods) {

            for (var x = 0; x < paymetMethods.length; x++) {
              var pmSelected = paymetMethods[x];
              var insertList = false;

              if ($pmFilter == 'credit') {
                if (pmSelected.payment_type_id == 'credit_card') {
                  insertList = true
                }
              } else if ($pmFilter == 'debit') {
                if (pmSelected.payment_type_id == 'debit_card' || pmSelected.payment_type_id == 'prepaid_card') {
                  insertList = true
                }
              } else {
                if (pmSelected.payment_type_id != 'credit_card' && pmSelected.payment_type_id != 'debit_card' && pmSelected.payment_type_id != 'prepaid_card') {
                  insertList = true
                }
              }

              if (insertList) {
                listPm.push({
                  src: pmSelected.secure_thumbnail,
                  name: pmSelected.name
                });
              }
            }
          }
        }

        return listPm;
      },

      /**
       * Mercado Pago Mini Logo
       * @returns {string|*}
       */
      getMercadopagoMini: function () {
        if (window.checkoutConfig.payment[this.getCode()] != undefined) {
          return window.checkoutConfig.payment[this.getCode()]['mercadopago_mini'];
        }
        return '';
      },
    });
  }
);
