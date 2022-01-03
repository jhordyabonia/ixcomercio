<?php namespace Extroniks\CatalogThemeInheritFix\Plugin;

abstract class AbstractPlugin {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Design
     */
    protected $catalogDesign;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\Design $catalogDesign
    ) {
        $this->registry = $registry;
        $this->themeProvider = $themeProvider;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->catalogDesign = $catalogDesign;
    }

    protected function getNormalizedThemeCode($themeId) {
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $this->themeProvider->getThemeById($themeId);
        $normalizedThemeCode = $theme->getCode();
        $normalizedThemeCode = str_replace('/', '_', $normalizedThemeCode);
        $normalizedThemeCode = str_replace('-', '_', $normalizedThemeCode);
        $normalizedThemeCode = strtolower($normalizedThemeCode);

        return $normalizedThemeCode;
    }

    /**
     *
     * @param array $handles
     * @return void
     */
    protected function addCustomHandles(array $handles) {
        $this->registry->register('catalog_theme_custom_layout_handles', $handles, true);
    }

}