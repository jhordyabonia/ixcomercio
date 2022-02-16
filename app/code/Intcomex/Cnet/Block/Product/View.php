<?php
declare(strict_types=1);

namespace Intcomex\Cnet\Block\Product;

use Intcomex\Cnet\Helper\Data as CnetHelper;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View as ProductView;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface as UrlEncoderInterface;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;

class View extends ProductView
{
    /**
     * Magento string lib
     *
     * @var StringUtils
     */
    protected $string;

    /**
     * @var JsonEncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var PriceCurrencyInterface
     * @deprecated 102.0.0
     */
    protected $priceCurrency;

    /**
     * @var UrlEncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var Product
     */
    protected $_productHelper;

    /**
     * @var ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CnetHelper
     */
    protected $cnetHelper;

    /**
     * @param Context $context
     * @param UrlEncoderInterface $urlEncoder
     * @param JsonEncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param CnetHelper $cnetHelper
     * @param array $data
     */
    public function __construct(
        Context                    $context,
        UrlEncoderInterface         $urlEncoder,
        JsonEncoderInterface       $jsonEncoder,
        StringUtils                $string,
        Product                    $productHelper,
        ConfigInterface            $productTypeConfig,
        FormatInterface            $localeFormat,
        Session                    $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface     $priceCurrency,
        CnetHelper                 $cnetHelper,
        array                      $data = []
    ) {
        $this->cnetHelper = $cnetHelper;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    /**
     * Returns needed data for Cnet platform.
     *
     * @return array
     */
    public function getCnetData():array
    {
        return [
            'mf' => $this->cnetHelper->getManufacturer(),
            'pn' => $this->getProduct()->getMpn(),
            'lang' => substr($this->cnetHelper->getLang(), 0, 2),
            'market' => $this->cnetHelper->getMarket(),
            '_Skey' => $this->cnetHelper->getSkey(),
            '_ZoneId' => $this->cnetHelper->getZoneId()
        ];
    }
}
