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
        try {
            $post  = $this->getRequest()->getPostValue();
            $cronFile = $this->crontab->getCronFile();
            $currentJobs = shell_exec('crontab -l');
            $finalJobs = $this->crontab->getFinalJobs($currentJobs, $post);
            file_put_contents($cronFile, $finalJobs);
            shell_exec("crontab $cronFile");
            $response = [
                'success' => true,
                'message' => __('Success!'),
                'crontab' => shell_exec('crontab -l')
            ];
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        $result = $this->resultJsonFactory->create();
        $result->setData($response);
        return $result;
    }
}
