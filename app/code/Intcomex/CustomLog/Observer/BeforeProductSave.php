<?php


namespace Intcomex\CustomLog\Observer;

use Magento\Framework\Event\ObserverInterface;


class BeforeProductSave implements ObserverInterface
{
    /**
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * Http Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;


    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->request = $request;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/manual_price_change.log');

        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $this->authSession = $authSession;
    }


    /**
     *
     *  @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();

        if ((!empty($product))) {
            $productId = $product->getId();

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $oldproduct = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);

            $user_admin = $this->authSession->getUser();
            if(!$user_admin||empty($user_admin)||$user_admin==null){
                $userName = 'ssh';
                $userEmail = 'trax:upload --name stock';
            }else{
                $userName = $user_admin->getUsername();
                $userEmail = $user_admin->getEmail();
            }

            if ($oldproduct->getData('price') != $product->getData('price')):

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
            endif;
        }
    }



}