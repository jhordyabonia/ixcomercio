<?php namespace Extroniks\CatalogThemeInheritFix\Plugin;

class CategoryViewPlugin extends AbstractPlugin
{

    public function beforeExecute(\Magento\Catalog\Controller\Category\View $subject)
    {
        $categoryId = (int) $subject->getRequest()->getParam('id', false);

        $category = $this->initCategory($categoryId);
        if ($category) {
            try {
                $settings = $this->catalogDesign->getDesignSettings($category);
                if ($settings->getCustomDesign()) {
                    $normalizedThemeCode = $this->getNormalizedThemeCode($settings->getCustomDesign());
                    $this->addCustomHandles(['catalog_category_view_' . $normalizedThemeCode]);
                    $this->catalogDesign->applyCustomDesign($settings->getCustomDesign());
                }
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return [];
    }

    /**
     *
     * @param $categoryId
     * @return \Magento\Catalog\Model\Category|CategoryInterface|bool
     */
    protected function initCategory($categoryId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Do nothing
            return false;
        }

        return $category;
    }
}