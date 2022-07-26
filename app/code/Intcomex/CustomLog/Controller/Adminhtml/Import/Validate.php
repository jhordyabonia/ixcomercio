<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Intcomex\CustomLog\Controller\Adminhtml\Import;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\ImportExport\Controller\Adminhtml\ImportResult as ImportResultController;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;

/**
 * Import validate controller action.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Validate extends ImportResultController implements HttpPostActionInterface
{
    /**
     * @var Import
     */
    private $import;

    /**
     * Validate uploaded files action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        /** @var $resultBlock Result */
        $resultBlock = $resultLayout->getLayout()->getBlock('import.frame.result');
        if ($data) {
            // common actions
            $resultBlock->addAction(
                'show',
                'import_validation_container'
            );

            /** @var $import \Magento\ImportExport\Model\Import */
            $import = $this->getImport()->setData($data);
            try {
                $source = $import->uploadFileAndGetSource();
                $erroPrice = array();
                if($data['entity']=='catalog_product'||$data['entity']=='advanced_pricing'){
                  $erroPrice =  $this->validatePrice($data,$source);
                }
                $this->processValidationResult($import->validateSource($source), $resultBlock,$erroPrice);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $resultBlock->addError($e->getMessage());
            } catch (\Exception $e) {
                $resultBlock->addError(__('Sorry, but the data is invalid or the file is not uploaded.'));
            }
            return $resultLayout;
        } elseif ($this->getRequest()->isPost() && empty($_FILES)) {
            $resultBlock->addError(__('The file was not uploaded.'));
            return $resultLayout;
        }
        $this->messageManager->addError(__('Sorry, but the data is invalid or the file is not uploaded.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }

    /**
     * Process validation result and add required error or success messages to Result block
     *
     * @param bool $validationResult
     * @param Result $resultBlock
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processValidationResult($validationResult, $resultBlock,$erroPrice)
    {
        $import = $this->getImport();
        $errorAggregator = $import->getErrorAggregator();
        if(!empty($erroPrice)){
            $resultBlock->addError(
                __('Productos con valores fuera de parametros '.implode(',',$erroPrice))
            );
            $this->addErrorMessages($resultBlock, $errorAggregator);
        }else{
            if ($import->getProcessedRowsCount()) {
                if ($validationResult) {
                    $this->addMessageForValidResult($resultBlock);
                } else {
                    $resultBlock->addError(
                        __('Data validation failed. Please fix the following errors and upload the file again.')
                    );
    
                    if ($errorAggregator->getErrorsCount()) {
                        $this->addMessageToSkipErrors($resultBlock);
                    }
                }
                $resultBlock->addNotice(
                    __(
                        'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                        $import->getProcessedRowsCount(),
                        $import->getProcessedEntitiesCount(),
                        $errorAggregator->getInvalidRowsCount(),
                        $errorAggregator->getErrorsCount()
                    )
                );
                
                $this->addErrorMessages($resultBlock, $errorAggregator);
            } else {
                if ($errorAggregator->getErrorsCount()) {
                    $this->collectErrors($resultBlock);
                } else {
                    $resultBlock->addError(__('This file is empty. Please try another one.'));
                }
            }
        }
    }

    /**
     * Provides import model.
     *
     * @return Import
     * @deprecated 100.1.0
     */
    private function getImport()
    {
        if (!$this->import) {
            $this->import = $this->_objectManager->get(Import::class);
        }
        return $this->import;
    }

    /**
     * Add error message to Result block and allow 'Import' button
     *
     * If validation strategy is equal to 'validation-skip-errors' and validation error limit is not exceeded,
     * then add error message and allow 'Import' button.
     *
     * @param Result $resultBlock
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addMessageToSkipErrors(Result $resultBlock)
    {
        $import = $this->getImport();
        if (!$import->getErrorAggregator()->hasFatalExceptions()) {
            $resultBlock->addSuccess(
                __('Please fix errors and re-upload file or simply press "Import" button to skip rows with errors'),
                true
            );
        }
    }

    /**
     * Add success message to Result block
     *
     * 1. Add message for case when imported data was checked and result is valid.
     * 2. Add message for case when imported data was checked and result is valid, but import is not allowed.
     *
     * @param Result $resultBlock
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addMessageForValidResult(Result $resultBlock)
    {
        if ($this->getImport()->isImportAllowed()) {
            $resultBlock->addSuccess(__('File is valid! To start import process press "Import" button'), true);
        } else {
            $resultBlock->addError(__('The file is valid, but we can\'t import it for some reason.'));
        }
    }

    /**
     * Collect errors and add error messages to Result block
     *
     * Get all errors from Error Aggregator and add appropriated error messages
     * to Result block.
     *
     * @param Result $resultBlock
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function collectErrors(Result $resultBlock)
    {
        $import = $this->getImport();
        $errors = $import->getErrorAggregator()->getAllErrors();
        foreach ($errors as $error) {
            $resultBlock->addError($error->getErrorMessage());
        }
    }

    public function validatePrice($data,$source){

        //\Intcomex\Auditoria\Helper\Email
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $storesRepository = $objectManager->get('\Magento\Store\Api\StoreRepositoryInterface');

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/validacion_precio_vacio.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->logger->info('Validación '.$data['entity']);
        
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        

        $style = 'style="border:1px solid"';

        $path = $this->getProtectedValue($this->getProtectedValue($source,'_file'),'path');
        $errorsSku = array();
        $errors = '';
        $colum = false;
        $colum2  = false;
        $productType = false;
        if (($handle = fopen($path, "r")) !== FALSE) {
            $csvFile = file($path);
            foreach ($csvFile as $key => $line) {
                if($key==0){
                    $dataLine = explode($data['_import_field_separator'],str_getcsv($line)[0]);
                    foreach ($dataLine as $keyData => $lineData) {
                        
                        if($lineData=='price'){
                            $colum = $keyData;
                        }
                        if($lineData=='special_price'){
                            $colum2 = $keyData;
                        }
                        if($lineData=='product_type'){
                            $productType = $keyData;
                        }
                    }
                }
            }
            $fila = 0;
            while (($datos = fgetcsv($handle, 10000, $data['_import_field_separator'])) !== FALSE) {
                if($fila>0){
                    
                    $sku = $datos[0];
                    $this->logger->info('Se evalua '.$sku.' para '.$datos[1]);
                    $this->logger->info(' ------- '); 
                    $this->logger->info($productType); 
                    $this->logger->info(' ------- '); 
                    $pType = $datos[$productType];
                    if($pType!='configurable'){
                        if($colum!=false){
                            $price = $datos[$colum];
                            if($price==''||empty($price)||$price==0){
                                    $errors .= '<tr>';
                                    $errors .= '<td '.$style.' >'.$sku.'</td>';
                                    $errors .= '<td '.$style.' >'.$datos[1].'</td>';
                                    $errors .= '<td '.$style.' >'.$price.'</td>';
                                    $errors .= '<td '.$style.' ></td>';
                                    $errors .= '</tr>';
                                    $errorsSku[] = $sku;
                            }
                        }
                        if($colum2!=false){
                            $special_price = $datos[$colum2];
                            if($special_price==''||empty($special_price)||$special_price==0){
                                    $errors .= '<tr>';
                                    $errors .= '<td '.$style.' >'.$sku.'</td>';
                                    $errors .= '<td '.$style.' >'.$datos[1].'</td>';
                                    $errors .= '<td '.$style.' ></td>';
                                    $errors .= '<td '.$style.' >'.$special_price.'</td>';
                                    $errors .= '</tr>';
                                    $errorsSku[] = $sku;
                            }
                        }
                    }
                }
                $fila ++;
            }

            $this->logger->info($errors);

            if($errors!=''){
                $helper = $objectManager->get('\Intcomex\CustomLog\Helper\Email');
                
                $templateId  = $scopeConfig->getValue('customlog/general/email_template');
                $extraError = $scopeConfig->getValue('customlog/general/mensaje_alerta');
                $email = explode(',',$scopeConfig->getValue('customlog/general/correos_alerta'));
                

                $variables = array(
                    'mensaje' => $extraError,
                    'body' => $errors
                );
                foreach($email as $key => $value){
                    if(!empty($value)){
                    $helper->notify(trim($value),$variables,$templateId);
                    }
                }
            }
            
            return $errorsSku;

        }
    }


    function getProtectedValue($obj, $name) {
        $array = (array)$obj;
        $prefix = chr(0).'*'.chr(0);
        return $array[$prefix.$name];
    }
}
