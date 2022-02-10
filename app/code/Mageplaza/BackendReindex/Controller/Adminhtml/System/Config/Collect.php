<?php

namespace Mageplaza\BackendReindex\Controller\Adminhtml\System\Config;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\DirectoryList;

class Collect extends Action
{
    /**
     * @const Path bin/magento.
     */
    const BIN_PATH = 'bin/magento';

    /**
     * @const Crontab filename.
     */
    const CRON_FILE = '/crontab.txt';

    /**
     * @const Magento command.
     */
    const INDEXER_COMMAND = 'indexer:reindex';

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        JsonFactory $resultJsonFactory
    ) {
        $this->directoryList = $directoryList;
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
                $rootPassword = $post['root_password'];
                $currentJobs = shell_exec("echo $rootPassword | sudo -S crontab -l");
                $finalJobs = $this->getFinalJobs($currentJobs, $post);

                $cronFile = $this->directoryList->getPath('var') . self::CRON_FILE;
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

    /**
     * Gets cron expression.
     *
     * @param array $params
     * @return string
     */
    private function getCronExpression(array $params): string
    {
        $time = explode(':', $params['time']);
        $cronExprArray = [
            intval($time[1]),
            intval($time[0]),
            $params['frequency'] === Frequency::CRON_MONTHLY ? '1' : '*',
            '*',
            $params['frequency'] === Frequency::CRON_WEEKLY ? '1' : '*',
        ];
        return join(' ', $cronExprArray);
    }

    /**
     * Gets Crontab Line.
     *
     * @param string $cronExpression
     * @param string $phpPath
     * @return string
     */
    private function getCrontabLine(array $params): string
    {
        return sprintf(
            '%s %s %s/%s %s',
            $this->getCronExpression($params),
            $params['php_path'],
            $this->directoryList->getRoot(),
            self::BIN_PATH,
            self::INDEXER_COMMAND
        );
    }

    /**
     * Gets Final Jobs string.
     *
     * @param $currentJobs
     * @param array $params
     * @return string
     */
    private function getFinalJobs($currentJobs, array $params): string
    {
        $arrayCurrentJobs = explode(PHP_EOL, $currentJobs);
        $finalJobs = '';
        $count = 0;
        $existCronIndexer = false;

        for ($i = 0; $i < count($arrayCurrentJobs); $i++) {
            $job = null;
            $count++;

            if ($arrayCurrentJobs[$i] && strpos($arrayCurrentJobs[$i], self::INDEXER_COMMAND)) {
                // Si el indexer existia lo reemplaza
                if ($params['enabled']) {
                    $job = $this->getCrontabLine($params);
                    $existCronIndexer = true;
                }
            } else {
                // Deja los cron que no tienen que ver con reindex
                $job = $arrayCurrentJobs[$i];
            }

            // Si el indexer no existia lo agrega
            if (!$existCronIndexer && $params['enabled'] && $count === count($arrayCurrentJobs)) {
                $job = $this->getCrontabLine($params);
            }

            if ($job) {
                $finalJobs .= $job . PHP_EOL;
            }
        }

        return $finalJobs;
    }
}
