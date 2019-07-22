/**
 * Pasarela_Bancomer Magento JS component
 *
 * @category    Bancomer
 * @package     Pasarela_Bancomer
 * @author      Valentina Aguirre
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
                type: 'pasarela_bancomer',
                component: 'Pasarela_Bancomer/js/view/payment/method-renderer/bancomer-form'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);