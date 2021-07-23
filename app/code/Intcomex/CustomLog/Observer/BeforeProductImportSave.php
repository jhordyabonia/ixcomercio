<?php


namespace Intcomex\CustomLog\Observer;

use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;


class BeforeProductImportSave implements ObserverInterface
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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var Action
     */
    private $massAction;


    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Action $massAction,
        array $data = []
    ) {
        $this->request = $request;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/imported_price_change.log');

        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $this->authSession = $authSession;

        $this->productRepository = $productRepository;

        $this->massAction = $massAction;
    }


    /**
     *
     *  @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if ($products = $observer->getEvent()->getBunch()) {

            $user_admin = $this->authSession->getUser();

            foreach ($products as $product){

                $product_old = $this->productRepository->get($product['sku']);

                $productId = $product_old->getId();

                $this->logger->info('Import Price - Product id: ' . $productId );
                $this->logger->info('Import Price - Product sku: ' . $product['sku']);
                $this->logger->info('Import Price - Product new ' . $product['price'] );
                if(!empty($user_admin)&&$user_admin!=null){
                    $this->logger->info('Import Price - User admin name: ' . $user_admin->getUsername() );
                    $this->logger->info('Import Price - User admin email: ' . $user_admin->getEmail() );
                }

                $this->logger->info('Import Price - Date: ' . date('Y-m-d G-i-s') );

            }
        }

    }



}