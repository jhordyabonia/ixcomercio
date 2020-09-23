define([
    'jquery',
    'uiRegistry',
    'mage/translate'
],function ($, registry, $t) {
    'use strict';

    return function (Component) {
        return Component.extend({
            validateShippingInformation: function () {
                var isFormValid = this._super(),
                    errors = [];

                if (!isFormValid) {
                    var messageContainer = registry.get('checkout.errors').messageContainer,
                        shippingMethodError = this.errorValidationMessage();

                    if (shippingMethodError) {
                        errors.push(shippingMethodError);
                    } else if (messageContainer.getErrorMessages().length) {
                        errors = $.merge(errors, messageContainer.getErrorMessages()());
                    }

                    if (!errors.length) {
                        errors.push($t('Missing required fields'));
                    }
                }

                $('body').trigger('mpCheckoutShippingStepValidation', [isFormValid, errors]);

                return isFormValid;
            }
        });
    }
});
