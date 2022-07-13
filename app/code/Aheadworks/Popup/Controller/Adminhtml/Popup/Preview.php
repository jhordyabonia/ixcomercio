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
namespace Aheadworks\Popup\Controller\Adminhtml\Popup;

use Magento\Backend\App\Action;

/**
 * Class Preview
 * @package Aheadworks\Popup\Controller\Adminhtml\Popup
 */
class Preview extends \Aheadworks\Popup\Controller\Adminhtml\Popup
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\Url
     */
    private $urlBuilder;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param \Magento\Framework\Url $urlBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Url $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    /**
     * Prepare popup for preview
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $content = $this->getRequest()->getParam('popup_content', null);
        $customCss = $this->getRequest()->getParam('custom_css', '');
        $effect = $this->getRequest()->getParam('effect', '');
        $position = $this->getRequest()->getParam('position', '');

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        if (null !== $content) {
            $params = [];
            $params['popupId'] = 1;
            $params['preview'] = true;
            $params['content'] = $content;
            $params['customCss'] = $customCss;
            $params['effect'] = $effect;
            $params['position'] = $position;

            $this->urlBuilder->addQueryParams(['preview' => 1, 'popup_info' => base64_encode(json_encode($params))]);
            $result['preview_url'] = $this->urlBuilder->getUrl('');
            $result['success'] = true;
        } else {
            $result['success'] = false;
        }

        return $resultJson->setData($result);
    }
}
