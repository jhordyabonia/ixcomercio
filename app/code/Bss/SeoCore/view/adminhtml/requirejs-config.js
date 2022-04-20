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
var config = {
    config: {
        mixins: {
            'Magento_AsynchronousOperations/js/grid/listing': {
                'Bss_SeoCore/js/grid/listing-mixin' :true
            },
            'Magento_Ui/js/grid/editing/editor': {
                'Bss_SeoCore/js/grid/editing/editor-mixin' :true
            }
        }
    }
};
