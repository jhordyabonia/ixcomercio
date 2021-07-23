/**
 * PagaloMasterCard for Magento JS component
 *
 * @category    PagaloMasterCard
 * @package     PagaloMasterCard
 * @author      PagaloMasterCard
 * @copyright   PagaloMasterCard (https://www.pagalo.com/)
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
                type: 'pagalomastercard',
                component: 'Magento_PagaloMasterCard/js/view/payment/method-renderer/stripe-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);