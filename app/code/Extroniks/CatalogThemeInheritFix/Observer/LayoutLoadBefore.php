<?php namespace Extroniks\CatalogThemeInheritFix\Observer;


class LayoutLoadBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Add a custom handle to category/product pages specific to custom themes
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $observer->getData('full_action_name');
        if (
            !in_array( $action, ['catalog_product_view', 'catalog_category_view'])
        ) {
            return $this;
        }

        $customLayoutHandles = $this->registry->registry('catalog_theme_custom_layout_handles');
        if( $customLayoutHandles && is_array($customLayoutHandles) ) {
            foreach($customLayoutHandles as $handle) {
                $layout = $observer->getData('layout');
                $layout->getUpdate()->addHandle($handle);
            }
        }

        return $this;
    }
}