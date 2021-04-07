define(
    [
        'ko',
        'uiComponent'
    ],
    function (ko, Component) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Intcomex_TradeIn/tradeinterms'
            },
            isRegisterNewsletter: true
        });
    }
);