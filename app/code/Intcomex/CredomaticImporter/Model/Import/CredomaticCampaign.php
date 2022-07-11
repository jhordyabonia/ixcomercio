<?php
declare(strict_types=1);

namespace Intcomex\CredomaticImporter\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magento\ImportExport\Helper\Data as ImportExportHelperData;
use Magento\ImportExport\Model\Import as ImportExport;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Intcomex\CredomaticImporter\Model\Import\Validator\ValidatorCredoCampaignInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data as ImportData;
use Intcomex\CredomaticMSI\Helper\UpdateFeeAttributeHelper;

class CredomaticCampaign extends AbstractEntity
{
    const INTCOMEX_CREDOMATIC = 'campaign_detail';
    const INTCOMEX_CREDOMATIC_CAMPAIGN = 'intcomex_credomatic_campaign';
    const CAMPAIGN_ID = 'campaign_id';
    const SKU = 'sku';
    const FEE = 'fee';
    const MAX_UNITS = 'max_units';
    const STATUS = 'status';

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * @var UpdateFeeAttributeHelper
     */
    private $updateFeeAttributeHelper;

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        self::CAMPAIGN_ID,
        self::SKU,
        self::FEE,
        self::MAX_UNITS,
        self::STATUS
    ];

    /**
     * @param JsonHelperData $jsonHelper
     * @param ImportExportHelperData $importExportData
     * @param ImportData $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param UpdateFeeAttributeHelper $updateFeeAttributeHelper
     */
    public function __construct(
        JsonHelperData $jsonHelper,
        ImportExportHelperData $importExportData,
        ImportData $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        UpdateFeeAttributeHelper $updateFeeAttributeHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_connection = $resource->getConnection();
        $this->errorAggregator = $errorAggregator;
        $this->updateFeeAttributeHelper = $updateFeeAttributeHelper;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode(): string
    {
        return self::INTCOMEX_CREDOMATIC_CAMPAIGN;
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
        if (!isset($rowData[self::CAMPAIGN_ID]) || empty($rowData[self::CAMPAIGN_ID])) {
            $this->addRowError(ValidatorCredoCampaignInterface::ERROR_IS_EMPTY_CAMPAIGN_ID, $rowNum);
        }
        if (!isset($rowData[self::SKU]) || empty($rowData[self::SKU])) {
            $this->addRowError(ValidatorCredoCampaignInterface::ERROR_IS_EMPTY_SKU, $rowNum);
        }
        if (!isset($rowData[self::FEE]) || empty($rowData[self::FEE])) {
            $this->addRowError(ValidatorCredoCampaignInterface::ERROR_IS_EMPTY_FEE, $rowNum);
        }
        if (!isset($rowData[self::MAX_UNITS]) || empty($rowData[self::MAX_UNITS])) {
            $this->addRowError(ValidatorCredoCampaignInterface::ERROR_IS_EMPTY_MAX_UNITS, $rowNum);
        }
        if (!isset($rowData[self::STATUS])) {
            $this->addRowError(ValidatorCredoCampaignInterface::ERROR_IS_EMPTY_STATUS, $rowNum);
        }

        // Validates if status column value is valid
        if ((string)$rowData[self::STATUS] !== '1' && (string)$rowData[self::STATUS] !== '0') {
            $this->addRowError(ValidatorCredoCampaignInterface::ERROR_FORMAT_STATUS, $rowNum);
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
                    $this->addRowError(ValidatorCredoCampaignInterface::ERROR_IS_EMPTY_SKU, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $values = [
                    self::CAMPAIGN_ID => trim($rowData[self::CAMPAIGN_ID]),
                    self::SKU => trim($rowData[self::SKU]),
                    self::FEE => trim($rowData[self::FEE]),
                    self::MAX_UNITS => trim($rowData[self::MAX_UNITS]),
                    self::STATUS => $rowData[self::STATUS]
                ];
                $data[][] = $values;
            }
        }

        $this->saveEntityFinish($data, self::INTCOMEX_CREDOMATIC);
    }

    /**
     * Saves data to campaign_detail table.
     *
     * @param array $entityData
     * @param $table
     * @return CredomaticCampaign
     */
    protected function saveEntityFinish(array $entityData, $table): CredomaticCampaign
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
                        $columns[] = 'hash';
                        $row['hash'] = $row['sku'] . $row['campaign_id'] . $row['fee'] ;
                        $this->_connection->insertOnDuplicate($tableName, [$row], $columns);
                        if ($row['status'] == 1) {
                            $this->updateFeeAttributeHelper->update($row, false);
                        }else{
                            $this->updateFeeAttributeHelper->delete($row);
                        }
                    }
                }
            } catch (Exception $e) {
                $this->addErrors($e->getMessage(), [1]);
            }
        }
        return $this;
    }
}
