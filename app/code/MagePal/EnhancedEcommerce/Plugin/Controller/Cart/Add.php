<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */
namespace MagePal\EnhancedEcommerce\Plugin\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use MagePal\EnhancedEcommerce\Model\Session as EnhancedEcommerceSession;

class Add
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var EnhancedEcommerce
     */
    protected $enhancedEcommerceSession;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param EnhancedEcommerceSession $enhancedEcommerceSession
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        EnhancedEcommerceSession $enhancedEcommerceSession
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->enhancedEcommerceSession = $enhancedEcommerceSession;
    }

    /**
     * Return omiture info
     *
     * @param Action $subject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Action $subject, $result)
    {
        if ($this->request->isAjax()) {
            $response = $this->response->getBody();

            $array = (array) json_decode($response, true);

            if ($itemAddToCart = $this->enhancedEcommerceSession->getItemAddToCart(true)) {
                if (!empty($itemAddToCart)) {
                    $array['enhancedecommerce'] = $itemAddToCart;
                }
            }

            $this->response->representJson(json_encode($array));
        }

        return $result;
    }
}
