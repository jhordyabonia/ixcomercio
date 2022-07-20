<?php
declare(strict_types=1);

namespace Intcomex\FreeShippingMessagev2\Controller\FreeShippingMessage;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Intcomex\FreeShippingMessagev2\Helper\FreeShippingMessage;
use Magento\Framework\Controller\ResultInterface;

class GetMessage extends Action  implements HttpGetActionInterface
{
    /**
     * @var FreeShippingMessage
     */
    private $freeShippingMessage;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @param FreeShippingMessage $freeShippingMessage
     * @param Context $context
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        FreeShippingMessage $freeShippingMessage,
        Context $context,
        RawFactory $resultRawFactory
    ) {
        $this->freeShippingMessage = $freeShippingMessage;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $result = $this->resultRawFactory->create();
        $result->setHeader('Content-Type', 'text/html');
        $result->setContents($this->freeShippingMessage->toHtml());
        return $result;
    }
}
