<?php
declare(strict_types=1);

namespace Intcomex\BinesImporter\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magento\ImportExport\Helper\Data as ImportExportHelperData;
use Magento\ImportExport\Model\Import as ImportExport;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Intcomex\BinesImporter\Model\Import\Validator\ValidatorBinCampaignInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data as ImportData;

class BinCampaign extends AbstractEntity
{
    const INTCOMEX_BINES = 'intcomex_bines';
    const INTCOMEX_BIN_CAMPAIGN = 'intcomex_bin_campaign';
    const CAMPAIGN = 'campaign';
    const BIN_CODES = 'bin_codes';
    const STATUS = 'status';

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        self::CAMPAIGN,
        self::BIN_CODES,
        self::STATUS
    ];

    /**
     * @param JsonHelperData $jsonHelper
     * @param ImportExportHelperData $importExportData
     * @param ImportData $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     */
    public function __construct(
        JsonHelperData $jsonHelper,
        ImportExportHelperData $importExportData,
        ImportData $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_connection = $resource->getConnection();
        $this->errorAggregator = $errorAggregator;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode(): string
    {
        return self::INTCOMEX_BIN_CAMPAIGN;
    }

    /**
     * Row validation data.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        // Validates mandatory columns
        if (!isset($rowData[self::CAMPAIGN]) || empty($rowData[self::CAMPAIGN])) {
            $this->addRowError(ValidatorBinCampaignInterface::ERROR_IS_EMPTY_CAMPAIGN, $rowNum);
        }
        if (!isset($rowData[self::BIN_CODES]) || empty($rowData[self::BIN_CODES])) {
            $this->addRowError(ValidatorBinCampaignInterface::ERROR_IS_EMPTY_BIN_CODES, $rowNum);
        }
        if (!isset($rowData[self::STATUS])) {
            $this->addRowError(ValidatorBinCampaignInterface::ERROR_IS_EMPTY_STATUS, $rowNum);
        }

        // Validates if status column value is valid
        if ((string)$rowData[self::STATUS] !== '1' && (string)$rowData[self::STATUS] !== '0') {
            $this->addRowError(ValidatorBinCampaignInterface::ERROR_FORMAT_STATUS, $rowNum);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import actions manager.
     *
     * @return bool
     */
    protected function _importData(): bool
    {
        if (ImportExport::BEHAVIOR_APPEND === $this->getBehavior()) {
            $this->importEntity();
        }
        return true;
    }

    /**
     * Shapes data from CSV file.
     */
    protected function importEntity()
    {
        $data = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorBinCampaignInterface::ERROR_IS_EMPTY_BIN_CODES, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $values = [
                    self::CAMPAIGN => trim($rowData[self::CAMPAIGN]),
                    self::BIN_CODES => trim($rowData[self::BIN_CODES]),
                    self::STATUS   => $rowData[self::STATUS]
                ];
                $data[][] = $values;
            }
        }

        $this->saveEntityFinish($data, self::INTCOMEX_BINES);
    }

    /**
     * Saves data to intcomex_bines table.
     *
     * @param array $entityData
     * @param $table
     * @return BinCampaign
     */
    protected function saveEntityFinish(array $entityData, $table): BinCampaign
    {
        if ($entityData) {
            try {
                $tableName = $this->_connection->getTableName($table);
                foreach ($entityData as $id => $entityRows) {
                    foreach ($entityRows as $key => $row) {
                        $columns = [];
                        foreach ($row as $column => $valor) {
                            $columns[] = $column;
                        }
                        $this->_connection->insertOnDuplicate($tableName, [$row], $columns);
                    }
                }
            } catch (Exception $e) {
                $this->addErrors($e->getMessage(), [1]);
            }
        }
        return $this;
    }
}
