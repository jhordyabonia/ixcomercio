<?php
declare(strict_types=1);

namespace Intcomex\ScheduledReindex\Helper;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;

class Crontab extends AbstractHelper
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
     * @param Context $context
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList
    ) {
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    /**
     * Gets Final Jobs string.
     *
     * @param $currentJobs
     * @param array $params
     * @return string
     */
    public function getFinalJobs($currentJobs, array $params): string
    {
        $finalJobs = '';
        if ($currentJobs) {
            $arrayCurrentJobs = explode(PHP_EOL, $currentJobs);
            $count = 0;
            $existCronIndexer = false;

            for ($i = 0; $i < count($arrayCurrentJobs); $i++) {
                $job = null;
                $count++;

                if ($arrayCurrentJobs[$i] && strpos($arrayCurrentJobs[$i], self::INDEXER_COMMAND)) {
                    // Si el indexer existia lo reemplaza
                    if ($params['enabled']) {
                        $job = $this->_getCrontabLine($params);
                        $existCronIndexer = true;
                    }
                } else {
                    // Deja los cron que no tienen que ver con reindex
                    $job = $arrayCurrentJobs[$i];
                }

                // Si el indexer no existia lo agrega
                if (!$existCronIndexer && $params['enabled'] && $count === count($arrayCurrentJobs)) {
                    $job = $this->_getCrontabLine($params);
                }

                if ($job) {
                    $finalJobs .= $job . PHP_EOL;
                }
            }
        }

        return $finalJobs;
    }

    /**
     * Gets file path to crontab.txt
     *
     * @return string
     * @throws FileSystemException
     */
    public function getCronFile(): string
    {
        return $this->directoryList->getPath('var') . self::CRON_FILE;
    }

    /**
     * Gets Crontab Line.
     *
     * @param array $params
     * @return string
     */
    private function _getCrontabLine(array $params): string
    {
        return sprintf(
            '%s %s %s/%s %s',
            $params['cron'],
            $params['php_path'],
            $this->directoryList->getRoot(),
            self::BIN_PATH,
            self::INDEXER_COMMAND
        );
    }
}
