<?php

namespace Intcomex\CustomMenu\Plugin;

class Topmenu
{
    protected $layout;
    /**
    * @param Context
    * @param array
    */
    public function __construct(
	    \Magento\Customer\Model\Session $session,
	   \Magento\Framework\View\LayoutInterface $layout
    ) {
	    $this->Session = $session;
	    $this->layout = $layout;
    }

    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $topmenu, $html)
    {
        $magentoCurrentUrl = $topmenu->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $html .= $this->layout->createBlock('Magento\Framework\View\Element\Template')->setTemplate('Magento_Theme::html/top-menu.phtml')->toHtml();
	return $html;
    }
}
