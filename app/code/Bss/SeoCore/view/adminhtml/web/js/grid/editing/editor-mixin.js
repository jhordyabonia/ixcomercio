/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_Breadcrumbs
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'Bss_SeoCore/js/model/mess'
], function ($, mess) {
    'use strict';

    var mixin = {
        /**
         * @inheritDoc
         */
        onDataSaved: function (data) {
            if (!data.error) {
                mess.hasMess(true);
                mess.mess(data.messages[0]);
            } else {
                mess.hasMess(undefined);
                mess.mess(undefined);
            }
            return this._super();
        }
    }

    return function (editorComponent) {
        return editorComponent.extend(mixin);
    }
});
