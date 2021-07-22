/**
 * Credomatic_Visa for Magento JS component
 *
 * @category    Credomatic_Visa
 * @package     Credomatic_Visa
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
                type: 'credomatic_visa',
                component: 'Intcomex_Credomatic_Visa/js/view/payment/method-renderer/stripe-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);