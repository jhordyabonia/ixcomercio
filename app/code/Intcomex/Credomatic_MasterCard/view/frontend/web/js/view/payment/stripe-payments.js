/**
 * Credomatic_MasterCard for Magento JS component
 *
 * @category    Credomatic_MasterCard
 * @package     Credomatic_MasterCard
 * @author      Intcomex
 * @copyright   Intcomex (https://www.intcomex.com/)
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'Credomatic_mastercard',
                component: 'Intcomex_Credomatic_MasterCard/js/view/payment/method-renderer/stripe-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);