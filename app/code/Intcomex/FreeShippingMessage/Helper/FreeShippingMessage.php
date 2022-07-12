<?php
declare(strict_types=1);

namespace Intcomex\FreeShippingMessage\Helper;

use Exception;
use Intcomex\FreeShippingMessage\Helper\Data as FreeShippingData;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Store\Model\StoreManagerInterface;

class FreeShippingMessage extends AbstractHelper
{
    const MISSING_AMOUNT = '{{missing_amount}}';

    /**
     * @var FreeShippingData
     */
    private $freeShippingData;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param Data $freeShippingData
     * @param Session $checkoutSession
     * @param PricingHelper $pricingHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        FreeShippingData $freeShippingData,
        Session $checkoutSession,
        PricingHelper $pricingHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->freeShippingData = $freeShippingData;
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return array|mixed|string|string[]
     */
    public function toHtml()
    {
        $data = array();
        try {
            if ($this->freeShippingData->isEnabled($this->storeManager->getStore()->getId())) {
                $message = $this->freeShippingData->getMessage();
                $amount = (float)$this->freeShippingData->getAmount();
                $grandTotal = $this->checkoutSession->getQuote()->getGrandTotal();
                $missingAmount = $amount - $grandTotal;
                if ($missingAmount >= 0) {
                    $formattedPrice = $this->pricingHelper->currency($missingAmount, true, false);
                    $data['msg'] = str_replace("%1", $formattedPrice, $message);
                    $data['bar_percent'] =  ( $grandTotal / $amount) * 100;
                }else{
                    $data['msg'] = $this->freeShippingData->getSuccessMessage();
                    $data['bar_percent'] =  100;
                }
            }
        } catch (Exception $e) {
            $data['msg'] = $e->getMessage();
        }
        
        return json_encode($data);
    }
}
