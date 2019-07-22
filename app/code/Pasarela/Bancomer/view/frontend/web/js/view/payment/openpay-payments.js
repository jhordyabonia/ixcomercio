/**
 * Pasarela_Bancomer Magento JS component
 *
 * @category    Bancomer
 * @package     Pasarela_Bancomer
 * @author      Federico Balderas
 * @copyright   Bancomer (http://openpay.mx)
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
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
                type: 'bancomer_multipagos',
                component: 'Pasarela_Bancomer/js/view/payment/method-renderer/cc-form'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);