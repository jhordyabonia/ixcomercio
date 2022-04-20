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
 * @category   BSS
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'Bss_SeoReport/js/model/elements-value'
], function (Val) {
    'use strict';

    var mixin = {
        setInitialValue: function () {
            this._super();
            if (this.inputName == 'content' || this.inputName == 'description') {
                Val.description(this.value());
            } else if (this.inputName == 'meta_description') {
                Val.metaDescription(this.value());
            }
            return this;
        }
    };

    return function (Abstract) {
        return Abstract.extend(mixin);
    };
});
