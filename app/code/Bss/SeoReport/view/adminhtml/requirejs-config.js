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
var config = {
    config: {
        mixins: {
            "Magento_Ui/js/form/element/abstract": {
                "Bss_SeoReport/js/form/element/abstract-mixin": true
            }
        }
    },
    shim: {
        "Bss_SeoReport/js/report_field": {
            deps: ["Magento_Ui/js/form/element/abstract", "Bss_SeoReport/js/model/elements-value"]
        }
    }
};
