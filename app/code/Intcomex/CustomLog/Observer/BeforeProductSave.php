<?php

namespace Intcomex\CustomLog\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\Exception;
use Zend\Log\Logger;

class BeforeProductSave implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @param Session $authSession
     */
    public function __construct(
        Session $authSession
    ) {
        $this->authSession = $authSession;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/manual_price_change.log');
        $this->logger = new Logger();
        $this->logger->addWriter($writer);
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $adminUser = $this->authSession->getUser();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');

        if ((!empty($product))) {
            $productId = $product->getId();
            $oldproduct = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);

            // If product is new not validate
            if (!$oldproduct->getId()) {
                $this->logger->info('BeforeProductSave ProductSku: ' . $product->getSku() . ' Producto nuevo no realiza validaciones.');
                return;
            }

            // Set user data
            if ($adminUser) {
                $userName = $adminUser->getUsername();
                $userEmail = $adminUser->getEmail();
            } else {
                $userName = 'ssh';
                $userEmail = 'trax:upload --name stock';
            }

            if ($oldproduct->getData('price') != $product->getData('price')) {
                $this->logger->info('Manual Price - Product id: ' . $productId );
                $this->logger->info('Manual Price - Product sku: ' .  $product->getSku());
                $this->logger->info('Manual Price - Product old ' . $oldproduct->getData('price') );
                $this->logger->info('Manual Price - Product new ' . $product->getData('price') );
                $this->logger->info('Manual Price - User admin name: ' . $userName );
                $this->logger->info('Manual Price - User admin email: ' . $userEmail );
                $this->logger->info('Manual Price - Date: ' . date('Y-m-d G-i-s') );
                $product->setData('update_manual','1');
                $product->setData('update_file','0');
                $product->setData('update_cron','0');
            }

            if ($product->getTypeId()!='configurable') {
                $errors = '';
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $websiteCode = $storeManager->getWebsite()->getCode();
    
                $style = 'style="border:1px solid"';
                $price = $product->getData('price');
                $special_price = $product->getData('special_price');
    
                $error = false;
                if ($price==''||empty($price)||$price==0) {
                    $error = true;
                }
                if ($special_price!=$oldproduct->getData('special_price')) {
                    if ($special_price==''||empty($special_price)||$special_price==0) {
                        $error = true;
                    }
                }
                if ($websiteCode!='base') {
                   // $error = true;
                }
    
                // If there is error
                if ($error) {
                    $helper = $objectManager->get('\Intcomex\CustomLog\Helper\Email');
                    $templateId  = $scopeConfig->getValue('customlog/general/email_template');
                    $extraError = $scopeConfig->getValue('customlog/general/mensaje_alerta');
                    $email = explode(',',$scopeConfig->getValue('customlog/general/correos_alerta'));

                    $errors .= '<tr>';
                    $errors .= '<td '.$style.' >'.$product->getSku().'</td>';
                    $errors .= '<td '.$style.' >'.$websiteCode.'</td>';
                    $errors .= '<td '.$style.' >'.$price.'</td>';
                    $errors .= '<td '.$style.' >'.$special_price.'</td>';
                    $errors .= '</tr>';
    
                    $variables = array(
                        'mensaje' => $extraError,
                        'body' => $errors
                    );
                    foreach ($email as $key => $value) {
                        if (!empty($value)) {
                            $helper->notify(trim($value),$variables,$templateId);
                        }
                    }

                    // If is admin user throw error || If is in CLI set log
                    if ($adminUser) {
                        throw new Exception(__($extraError));
                    } else {
                        $this->logger->info('Price Error ProductId: ' . $productId . ' Extra Error: ' . $extraError);
                    }
                }
            }
        }
    }
}
