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
                console.log("method custom xiaomicl");

                var menu = $("#payment_methods_menu").find('ul');

                var title_cont = $(".payment-method-title.custom_method");

                var title = $(title_cont).find('label.label span').text();
                var input = $(title_cont).find('input');
                var code_payment = $(input).attr('id');


                $(menu).prepend('<li role="presentation" class="payment-group-item debitcard active"><a id="link-' + code_payment + '" data-code="' + code_payment + '">' + title + '</a><img src="'+window.franquiciamp+'" ></li>');

                $('#' + code_payment).trigger("click");

                $(document).on('click', `#payment_methods_menu ul li a#link-` + code_payment, function (event) {

                    var data = $(this).attr('data-code');

                    $('#' + data).trigger("click");

                    if ($(this).parent().hasClass('active')) {

                    } else {
                        $(menu).find('li.active').removeClass('active');
                        $(this).parent().addClass('active');
                    }

                });


            },
            /**
         * Get list of available month values
         * @returns {Object}
         */
            getCcMonthsValues: function () {
                console.log('test Rewrite getCcMonthsValues');
                return _.map(this.getCcMonths(), function (value, key) {
                    return {
                        'value': key,
                        'month': value
                    };
                });
            },
            /**
         * Get list of months
         * @returns {Object}
         */
            getCcMonths: function () {
                var ccMonths = window.checkoutConfig.payment.ccform.months[this.getCode()];
                var newCcMonths = new Object;
                for (const i in ccMonths) {
                    if (i <= 12) {
                        newCcMonths[i] = ccMonths[i];
                    }
                }
                return newCcMonths;
            },
            /**
         * Get list of available year values
         * @returns {Object}
         */
            getCcYearsValues: function () {
                return _.map(this.getCcYears(), function (value, key) {
                    return {
                        'value': key,
                        'year': value
                    };
                });
            },
            /**
             * Get list of years
             * @returns {Object}
             */
            getCcYears: function () {
                var ccYears = window.checkoutConfig.payment.ccform.years[this.getCode()];
                var newCcYears = new Object;
                var count = 1;
                for (var j in ccYears) {
                    if (count <= 11) {
                        newCcYears[j] = ccYears[j];
                    }
                    count++;
                }
                return newCcYears;
            },
        });
    }
});