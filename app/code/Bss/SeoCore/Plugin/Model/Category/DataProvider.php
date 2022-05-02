<?php
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
 * @package    Bss_SeoCore
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoCore\Plugin\Model\Category;

class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * @return array
     */
    public function getSeoFields()
    {
        return [
            'custom_attribute',
            'seo_h1_title',
            'excluded_meta_template',
            'excluded_html_sitemap',
            'excluded_xml_sitemap'
        ];
    }

    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        $fieldsMap = parent::getFieldsMap();
        if (isset($fieldsMap['search_engine_optimization'])) {
            $seoFields = $this->getSeoFields();
            foreach ($seoFields as $field) {
                $fieldsMap['search_engine_optimization'][] = $field;
            }
        }
        return $fieldsMap;
    }
}
