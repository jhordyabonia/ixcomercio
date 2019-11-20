<?php
namespace Mienvio\Api\Cron;
use \Psr\Log\LoggerInterface;

class DeliveryDetail {

    const API_KEY = 'trax_general/catalogo_retailer/apikey';

	const ACCESS_KEY = 'trax_general/catalogo_retailer/accesskey';

	const ENVIROMENT = 'trax_general/catalogo_retailer/apuntar_a';

	const URL_DESARROLLO = 'trax_general/catalogo_retailer/url_desarrollo';

	const URL_PRODUCCION = 'trax_general/catalogo_retailer/url_produccion';

	const TIMEOUT = 'trax_general/catalogo_retailer/timeout';

	const ERRORES = 'trax_general/catalogo_retailer/errores';

    const DATOS_TRAX = 'trax_catalogo/catalogo_general/datos_iws';

    const DATOS_SALES_TRAX = 'trax_catalogo/catalogo_general/datos_sales_iws';

    const DATOS_IMAGES_TRAX = 'trax_catalogo/catalogo_general/datos_images_iws';

    const CATALOGO_REINTENTOS = 'trax_catalogo/catalogo_general/catalogo_reintentos';

    const CATALOGO_CORREO = 'trax_catalogo/catalogo_general/catalogo_correo';

    const TAX_ID = 'trax_catalogo/catalogo_general/tax_id';

    const ATTRIBUTE_ID = 'trax_catalogo/catalogo_general/attribute_id';

    const PRODUCT_NAME = 'trax_catalogo/catalogo_iws/product_name';

    const PRODUCT_DESCRIPTION = 'trax_catalogo/catalogo_iws/product_description';

    const PRODUCT_WEIGHT = 'trax_catalogo/catalogo_iws/product_weight';

    const PRODUCT_LENGTH = 'trax_catalogo/catalogo_iws/product_length';

    const PRODUCT_WIDTH = 'trax_catalogo/catalogo_iws/product_width';

    const PRODUCT_HEIGHT = 'trax_catalogo/catalogo_iws/product_height';

    const PRODUCT_PRICE = 'trax_catalogo/catalogo_iws/product_price';

    const PRODUCT_STOCK = 'trax_catalogo/catalogo_iws/product_stock';
    
    private $helper;
	
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    protected $logger;

    protected  $productRepository;     

    public function __construct(LoggerInterface $logger, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,     \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool, \Magento\Indexer\Model\IndexerFactory $indexerFactory,     \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory, \Mienvio\Api\Helper\Email $email) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
        $this->helper = $email;
    }

/**
   * Write to system.log
   *
   * @return void
   */

    public function execute() 
    {

    }

}