<?php
/**
 * Aheadworks Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://ecommerce.aheadworks.com/end-user-license-agreement/
 *
 * @package    Popup
 * @version    1.2.3
 * @copyright  Copyright (c) 2020 Aheadworks Inc. (http://www.aheadworks.com)
 * @license    https://ecommerce.aheadworks.com/end-user-license-agreement/
 */
namespace Aheadworks\Popup\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Popup
 * @package Aheadworks\Popup\Model
 */
class Popup extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'aw_popup';

    /**
     * Rule
     *
     * @var \Magento\Rule\Model\AbstractModel
     */
    private $_rule = null;

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Aheadworks\Popup\Model\ResourceModel\Popup::class);
    }

    /**
     * Get rule model
     *
     * @return \Aheadworks\Popup\Model\Rule\Product
     */
    public function getRuleModel()
    {
        if (null === $this->_rule) {
            $ruleModel = \Magento\Framework\App\ObjectManager::getInstance()
               ->create(\Aheadworks\Popup\Model\Rule\Product::class);
            $this->_rule = $ruleModel;
        }
        return $this->_rule;
    }

    /**
     * Set up prepared conditions to model
     *
     * @param array $data
     * @param array $allowedKeys
     *
     * @return $this
     */
    public function loadPost($data, $allowedKeys)
    {
        if (empty($data)) {
            $this->setData('popup_conditions', '');
            return $this;
        }
        $conditions = $this->_convertFlatToRecursive($data, $allowedKeys);
        $this->setData('popup_conditions', $conditions['popup'][1]);

        return $this;
    }

    /**
     * Converted conditions
     *
     * @param array $data
     * @param array $allowedKeys
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convertFlatToRecursive(array $data, $allowedKeys = [])
    {
        $arr = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys) && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $arr;

                    for ($i = 0, $l = count($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                /**
                 * Convert dates into \DateTime
                 */
                if (in_array($key, ['from_date', 'to_date']) && $value) {
                    $value = new \DateTime($value);
                }
                $this->setData($key, $value);
            }
        }
        return $arr;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
