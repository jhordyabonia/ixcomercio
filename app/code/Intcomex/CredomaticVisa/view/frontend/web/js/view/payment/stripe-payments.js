/**
 * Credomatic for Magento JS component
 *
 * @category    Credomatic
 * @package     Credomatic
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
                type: 'credomaticvisa',
                component: 'Intcomex_CredomaticVisa/js/view/payment/method-renderer/stripe-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);