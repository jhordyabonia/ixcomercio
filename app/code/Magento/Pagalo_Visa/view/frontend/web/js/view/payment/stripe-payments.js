/**
 * Pagalo_Visa for Magento JS component
 *
 * @category    Pagalo_Visa
 * @package     Pagalo_Visa
 * @author      Pagalo_Visa
 * @copyright   Pagalo_Visa (https://www.pagalo.com/)
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
                type: 'pagalo_visa',
                component: 'Magento_Pagalo_Visa/js/view/payment/method-renderer/stripe-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);