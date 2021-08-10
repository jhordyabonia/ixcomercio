/**
 * PagaloVisa for Magento JS component
 *
 * @category    PagaloVisa
 * @package     PagaloVisa
 * @author      PagaloVisa
 * @copyright   PagaloVisa (https://www.pagalo.com/)
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
                type: 'pagalovisa',
                component: 'Magento_PagaloVisa/js/view/payment/method-renderer/stripe-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);