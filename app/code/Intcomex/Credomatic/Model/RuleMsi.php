<?php
namespace Intcomex\Credomatic\Model;
use Intcomex\CredomaticMSI\Model\ResourceModel\Campaign\CollectionFactory;
class RuleMsi
{

    /**
     * @var CollectionFactory
     */
    private $_collection;

      /**
     * @var \Zend\Log\Logger
     */
	protected $logger;

    public function __construct(
        CollectionFactory $collection
    )
    {
        $this->_collection = $collection;
    }

    public function applyRule($quote, $configValue)
    {
        $detailsCampaign = $this->_collection->create()->getDetailsCampaignActive()->getData();
        $product = [];

        foreach ($quote as $item) {
            $product []= [
                "sku" => $item->getSku(),
                "qty" => $item->getQty()
            ];
        }

        $arrayConf = explode(',', $configValue);

        $getFee = $this->getFee($product, $detailsCampaign, $arrayConf);

        if (empty($getFee)) {
            unset($arrayConf[array_search(18, $arrayConf)]);
            unset($arrayConf[array_search(24, $arrayConf)]);
            $configFee = implode(",", $arrayConf);
        }else{
            $configFee = implode(",", $getFee);
        }
        return $configFee;
    }
    
    public function getFee($quoteSkus, $detailsCampaign, $arrayConf)
    {
        $arraProduct = [];
        $arraFee = [];
        $arraSku = [];
        $arraApllyCamp = [];

        foreach ($detailsCampaign as $detailCam) {
            $arraSku[] = $detailCam['sku'];
            foreach ($quoteSkus as $key => $quoteSku) {
                if ($detailCam['sku'] == $quoteSku['sku'] && $quoteSku['qty'] <= $detailCam['max_units']) {
                    if (isset($arraProduct[$quoteSku['sku']]) && $detailCam['fee'] > $arraProduct[$quoteSku['sku']]) {
                        $arraProduct[$quoteSku['sku']] = $detailCam['fee'];
                    }
                    if (!isset($arraProduct[$quoteSku['sku']])) {
                        $arraProduct[$quoteSku['sku']] = $detailCam['fee'];
                    }
                }
            }
        }

        //check if all products apply to the campaign.
        foreach ($quoteSkus as $key => $sku) {
            if (in_array($sku['sku'], $arraSku)) {
                $arraApllyCamp[] = 1;
            } else {
                $arraApllyCamp[] = 0;
            }
        }

        if (in_array(0, $arraApllyCamp)) {
            return [];
        }

        foreach ($arraProduct as $fee) {
            $arraFee[] = $fee;
        }

        if (count($arraFee)) {
            if (min($arraFee) !== max($arrayConf)) {
                $arrayNewConfig = [];
                foreach ($arrayConf as $conf) {
                    if ($conf > min($arraFee)) {
                        continue;
                    } else {
                        $arrayNewConfig[] = $conf;
                    }
                }
                $arrayConf = $arrayNewConfig;
            }
        }

        return $arrayConf;
    }
}