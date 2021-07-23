/**
 * CredomaticVisa for Magento JS component
 *
 * @category    CredomaticVisa
 * @package     CredomaticVisa
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
                component: 'Intcomex_CredomaticVisa/js/view/payment/method-renderer/stripe-method-visa'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);