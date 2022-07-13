define(['jquery'], function ($) {
    'use strict';

    var validationWidgetMixin = {
        options: {
            ignore: ':disabled, .ignore-validate, .no-display.template, ' +
                ':disabled input, .ignore-validate input, .no-display.template input, ' +
                ':disabled select, .ignore-validate select, .no-display.template select, ' +
                ':disabled textarea, .ignore-validate textarea, .no-display.template textarea, ' +
                '[name=form_key], [name=uenc], [name=product]'
        }

    };

    /**
     * Add new ignore elements for validator in product forms inside Page Builder Content
     */
    return function (validationWidget) {
        $.widget('mage.validation', validationWidget, validationWidgetMixin);

        return $.mage.validation;
    };
});
