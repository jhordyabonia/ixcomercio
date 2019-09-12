<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Zendesk\Controller\Adminhtml\App\Ajax;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Install extends Action
{
    const ZENDESK_M2_APP_ID = "136787";

    /**
     * @var \Wagento\Zendesk\Helper\Api\Apps
     */
    private $apps;
    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    private $integrationService;
    /**
     * @var \Magento\Integration\Api\OauthServiceInterface
     */
    private $oauthService;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Install constructor.
     * @param Action\Context $context
     * @param \Wagento\Zendesk\Helper\Api\Apps $apps
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param \Magento\Integration\Api\OauthServiceInterface $oauthService
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Action\Context $context,
        \Wagento\Zendesk\Helper\Api\Apps $apps,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        \Magento\Integration\Api\OauthServiceInterface $oauthService,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {

        parent::__construct($context);
        $this->apps = $apps;
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        // ajax validation
        if (!$this->getRequest()->isAjax()) {
            return $this->_forward('noroute');
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $res = [];

        /*
         * MAGENTO REST INTEGRATION
         */
        $integrationData = [
            'name' => 'm2appzendesk',
            'email' => '',
            'endpoint' => '',
            'identity_link_url' => '',
            'current_password' => '',
            'all_resources' => '',
            'resource' => [
                'Wagento_Zendesk::zendesk_api'
            ],
        ];

        /** @var \Magento\Integration\Model\Integration $integration */
        $integration = $this->integrationService->findByName('m2appzendesk');
        if ($integration->getConsumerId()) {
            // Update existing integration
            $finalData = array_merge($integration->getData(), $integrationData);
            $integration = $this->integrationService->update($finalData);
        } else {
            //Create new integration
            $integration = $this->integrationService->create($integrationData);
        }

        // Activate integration if status is inactive
        $integrationId = $integration->getConsumerId();
        if ($integration->getConsumerId() && !$integration->getStatus()) {
            try {
                $clearExistingToken = (int)$this->getRequest()->getParam('reauthorize', 0);
                if ($this->oauthService->createAccessToken($integrationId, $clearExistingToken)) {
                    $integration->setStatus(\Magento\Integration\Model\Integration::STATUS_ACTIVE)->save();
                }
            } catch (\Exception $exception) {
                return $resultJson->setData(['error' => 'Something wrong has happened']);
            }
        }

        /*
         * ZENDESK APP INSTALLATION
         */
        $app_array = $this->apps->listAppInstallations();
        $apps = array_column($app_array, 'app_id', 'id');

        // Prepare parameters to install or update
        /** @var \Magento\Integration\Model\Oauth\Token $accessToken */
        $accessToken = $this->oauthService->getAccessToken($integrationId);
        $appParams = [
            'domain' => $this->getFrontBaseUrl(),
            'token' => $accessToken->getToken(),
        ];

        // Check if we already have app installed
        if ($installationId = array_search(self::ZENDESK_M2_APP_ID, $apps)) {
            $updateId = $this->apps->updateApp($installationId, $appParams);
            $res = is_numeric($updateId) ? ['success' => 'Succesfully updated'] : ['error' => 'Can\'t update app.'];
        } else {
            // Install App
            $appParams['name'] = 'Magento 2 Connector by Wagento';
            $installationId = $this->apps->installApp(self::ZENDESK_M2_APP_ID, $appParams);
            $res = is_numeric($installationId) ? ['success' => 'Succesfully installed'] : ['error' => 'Can\'t install app.'];
        }

        return $resultJson->setData($res);
    }

    /**
     * @return mixed
     */
    private function getFrontBaseUrl()
    {
        // REPEATED refactor needed
        return $this->scopeConfig->getValue('web/secure/base_url');
    }
}
