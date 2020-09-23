define([
    'jquery'
],function ($) {
    'use strict';

    return function (Component) {
        return Component.extend({
            placeOrder: function () {
                var isFormValid = this._super(),
                    errors = [],
                    $alertDiv = $('aside.confirm div.modal-content div');

                if (!isFormValid && $alertDiv.length) {
                    errors.push($alertDiv.text());
                }

                $('body').trigger('mpCheckoutPaymentStepValidation', isFormValid, errors);

                return isFormValid;
            }
        });
    }
});
