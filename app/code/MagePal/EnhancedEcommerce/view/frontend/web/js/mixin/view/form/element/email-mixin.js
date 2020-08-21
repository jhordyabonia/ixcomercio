define([
    'jquery'
],function ($) {
    'use strict';

    return function (Component) {
        return Component.extend({
            checkEmailAvailability: function () {
                this._super();

                $.when(this.checkRequest).done(function (isEmailAvailable) {
                    $('body').trigger(
                        'mpCheckoutEmailValidation',
                        !isEmailAvailable
                    );
                });
            }
        });
    }
});
