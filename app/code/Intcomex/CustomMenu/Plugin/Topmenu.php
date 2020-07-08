<?php

namespace Test\TestCustomMenu\Plugin;

class Topmenu
{
    /**
    * @param Context
    * @param array
    */
    public function __construct(
        \Magento\Customer\Model\Session $session
    ) {
        $this->Session = $session;
    }

    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $topmenu, $html)
    {
        $swappartyUrl = $topmenu->getUrl('testCustomMenu/custommenu');
        $magentoCurrentUrl = $topmenu->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        if (strpos($magentoCurrentUrl,'testCustomMenu/custommenu') !== false) {
            $html .= "<li class=\"level0 nav-5 active level-top parent ui-menu-item\">";
        } else {
            $html .= "<li class=\"level0 nav-4 level-top parent ui-menu-item\">";
        }
        $html .= "<a href=\"" . $swappartyUrl . "\" class=\"level-top ui-corner-all\"><span class=\"ui-menu-icon ui-icon ui-icon-carat-1-e\"></span><span>" . __("Custom Menu") . "</span></a>";
        $html .= "</li>";
        return $html;
    }
}