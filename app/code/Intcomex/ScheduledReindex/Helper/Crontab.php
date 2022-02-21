<?php
declare(strict_types=1);

namespace Intcomex\ScheduledReindex\Helper;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
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
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        EncryptorInterface $encryptor
    ) {
        $this->directoryList = $directoryList;
        $this->encryptor = $encryptor;
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
     * Gets Password Decrypt.
     *
     * @param $password
     * @return string
     */
    public function getDecryptPassword($password): string
    {
        return $this->encryptor->decrypt($password);
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
            $this->_getCronExpression($params),
            $params['php_path'],
            $this->directoryList->getRoot(),
            self::BIN_PATH,
            self::INDEXER_COMMAND
        );
    }

    /**
     * Gets cron expression.
     *
     * @param array $params
     * @return string
     */
    private function _getCronExpression(array $params): string
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
}
