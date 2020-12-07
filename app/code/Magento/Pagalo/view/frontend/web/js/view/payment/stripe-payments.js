/**
 * Pagalo for Magento JS component
 *
 * @category    Pagalo
 * @package     Pagalo
 * @author      Pagalo
 * @copyright   Pagalo (https://www.pagalo.com/)
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
                type: 'pagalo',
                component: 'Magento_Pagalo/js/view/payment/method-renderer/stripe-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);