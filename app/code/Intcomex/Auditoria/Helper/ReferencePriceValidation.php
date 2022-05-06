<?php

namespace Intcomex\Auditoria\Helper;

use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\Exception;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ReferencePriceValidation
{
    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var Email
     */
    private $helper;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Session $authSession
     * @param Email $email
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $authSession,
        Email $email,
        ScopeConfigInterface $scopeConfig,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->authSession = $authSession;
        $this->helper = $email;
        $this->scopeConfig = $scopeConfig;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/reference_price.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
    }

    /**
     * @param Product $productNew
     * @param $price
     * @param $specialPrice
     * @param string $websiteCode
     * @param int $storeId
     * @return array|true
     */
    public function execute(Product $productNew, $price, $specialPrice, string $websiteCode, int $storeId)
    {
        $isActive = (bool)$this->scopeConfig->getValue('auditoria/general/enabled', ScopeInterface::SCOPE_STORE, $storeId);

        if ($isActive) {
            $style = 'style="border:1px solid"';
            $errors = '';
            $productFactory = $this->productFactory->create();
            $productFactory->setStoreId($storeId);
            $productOld = $productFactory->loadByAttribute('sku',trim($productNew->getSku()));
            $percentage = $this->scopeConfig->getValue('auditoria/general/porcentaje_validacion', ScopeInterface::SCOPE_STORE, $storeId);

            if (!$productOld) {
                return true;
            }

            $price = str_replace(',', '', $price ?? $productOld->getPrice());
            $specialPrice = str_replace(',', '', $specialPrice ?? $productOld->getSpecialPrice());

            $referencePrice = $productOld->getPrecioReferencia();
            $priceMinusPercentage =  $referencePrice - (($referencePrice * (int)$percentage) / 100);

            $this->logger->info('Sku: '.$productNew->getSku().' Store: '.$websiteCode);
            $this->logger->info('Price: ' . $price . ' SpecialPrice: ' . $specialPrice . ' ReferencePrice: ' . $referencePrice);
            $this->logger->info('Precio a validar: ' . $referencePrice . ' - ((' . $referencePrice . ' * ' . $percentage . ') / 100) = ' . $priceMinusPercentage);

            if ($referencePrice > 0 && $referencePrice !== '' && !empty($referencePrice)) {
                if (($price && $price < $priceMinusPercentage) || ($specialPrice && $specialPrice < $priceMinusPercentage)) {
                    $errors .= '<tr>';
                    $errors .= '<td '.$style.' >'.$productOld->getSku().'</td>';
                    $errors .= '<td '.$style.' >'.$referencePrice.'</td>';
                    $errors .= '<td '.$style.' >'.$price.'</td>';
                    $errors .= '<td '.$style.' >'.$specialPrice.'</td>';
                    $errors .= '<td '.$style.' >'.$this->getCurrentUser().'</td>';
                    $errors .= '</tr>';
                    $this->logger->info('Sku: '.$productNew->getSku().' - Error, precio de referencia por fuera del rango permitido.');
                }
            } else {
                $this->logger->info('No se puede evaluar  '.$productNew->getSku().' No tiene precio referencia en: '.$websiteCode);
            }

            if ($errors !== '') {
                return [
                    'errors'  => $errors,
                    'website' => $websiteCode,
                    'store'   => $storeId
                ];
            }
        }

        return true;
    }

    /**
     * @param string $errors
     * @param string $websiteCode
     * @param int|null $storeId
     */
    public function sendReferencePriceErrorEmail(string $errors, string $websiteCode, int $storeId = null)
    {
        try {
            $this->helper->notify($errors, $websiteCode, $storeId);
        } catch (\Exception $e) {
            $this->logger->info('Error Sending Reference Price Error: ' . $e->getMessage());
        }
    }

    /**
     * @return mixed|string
     */
    private function getCurrentUser()
    {
        return $this->authSession->getUser() ? $this->authSession->getUser()->getUserName() : 'Cronjob';
    }
}
