<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ViewAction extends Column
{
    /**
     * @var \Wagento\Zendesk\Helper\Data
     */
    private $zendeskHelper;

    /**
     * ViewAction constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Wagento\Zendesk\Helper\Data $zendeskHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Wagento\Zendesk\Helper\Data $zendeskHelper,
        array $components = [],
        array $data = []
    ) {
    
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->zendeskHelper = $zendeskHelper;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['id'])) {
                    $name = $this->getData('name');
                    $item[$name] = [
                        'view' => [
                            'href' => $this->getTicketUrl($item['id']),
                            'label' => __('View'),
                            'target' => '_blank'
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param $id
     * @return string
     */
    private function getTicketUrl($id)
    {
        return $this->zendeskHelper->getUrl('ticket', $id);
    }
}
