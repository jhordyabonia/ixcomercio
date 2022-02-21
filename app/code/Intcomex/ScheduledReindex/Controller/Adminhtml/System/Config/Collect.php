<?php
declare(strict_types=1);

namespace Intcomex\ScheduledReindex\Controller\Adminhtml\System\Config;

use Exception;
use Intcomex\ScheduledReindex\Helper\Crontab;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Collect extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Crontab
     */
    protected $crontab;

    /**
     * @param Context $context
     * @param Crontab $crontab
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Crontab $crontab,
        JsonFactory $resultJsonFactory
    ) {
        $this->crontab = $crontab;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Sets crontab.
     *
     * @return Json
     * @throws Exception
     */
    public function execute(): Json
    {
        $post  = $this->getRequest()->getPostValue();

        if (isset($post['root_password']) && !empty($post['root_password'])) {
            try {
                $rootPassword = $this->crontab->getDecryptPassword($post['root_password']);
                $currentJobs = shell_exec("echo $rootPassword | sudo -S crontab -l");
                $finalJobs = $this->crontab->getFinalJobs($currentJobs, $post);
                $cronFile = $this->crontab->getCronFile();

                file_put_contents($cronFile, $finalJobs);
                exec("echo $rootPassword | sudo -S crontab $cronFile");

                $response = ['success' => true, 'message' => 'Success!', 'crontab' => $finalJobs];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        } else {
            $response = ['success' => false, 'message' => 'Required Root Password!'];
        }

        $result = $this->resultJsonFactory->create();
        $result->setData($response);
        return $result;
    }
}
