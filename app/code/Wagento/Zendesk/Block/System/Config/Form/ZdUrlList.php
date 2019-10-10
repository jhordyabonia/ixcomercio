<?php

namespace Wagento\Zendesk\Block\System\Config\Form;

class ZdUrlList extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var \Magento\Store\Api\Data\StoreInterface[] $stores */
        $stores = $this->_storeManager->getStores();
        $valueReturn = '';
        $urlArray = [];

        foreach ($stores as $store) {
            $baseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true);
            if ($baseUrl) {
                $v1 = $baseUrl . 'zendesk';
                $v2 = $baseUrl . 'zendesk/';
                $urlArray[] = "<div>" . $this->escapeHtml($v1) . "</div>";
                $urlArray[] = "<div>" . $this->escapeHtml($v2) . "</div>";
            }
        }

        $urlArray = array_unique($urlArray);
        foreach ($urlArray as $uniqueUrl) {
            $valueReturn .= "<div>" . $uniqueUrl . "</div>";
        }

        return '<td class="value">' . $valueReturn . '</td>';
    }
}