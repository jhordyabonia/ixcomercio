/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'PasarelaCheckout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'pasarela_bancomer',
                component: 'PasarelaBancomer/js/view/payment/method-renderer/pasarela_bancomer'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
