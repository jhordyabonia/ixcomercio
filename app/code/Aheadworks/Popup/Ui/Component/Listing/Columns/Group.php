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
namespace Aheadworks\Popup\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Group
 * @package Aheadworks\Popup\Ui\Component\Listing\Columns
 */
class Group extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Customer group factory
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_customerGroupFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_customerGroupFactory = $groupFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            $item['customer_groups'] = $this->_prepareContent($item['customer_groups']);
        }

        return $dataSource;
    }

    /**
     * Prepare content
     *
     * @param string $customerGroup
     * @return string
     */
    protected function _prepareContent($customerGroup)
    {
        $content = [];
        $customerGroup = explode(',', $customerGroup);
        if (!is_array($customerGroup)) {
            $customerGroup = [$customerGroup];
        }
        foreach ($customerGroup as $groupId) {
            $groupModel = $this->_customerGroupFactory->create();
            $groupModel->load($groupId);
            if (null === $groupModel->getId()) {
                continue;
            }
            $content[] = $groupModel->getCode();
        }

        return implode(', ', $content);
    }
}
