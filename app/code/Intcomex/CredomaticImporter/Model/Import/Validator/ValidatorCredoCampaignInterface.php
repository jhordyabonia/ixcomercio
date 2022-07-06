<?php
declare(strict_types=1);

namespace Intcomex\CredomaticImporter\Model\Import\Validator;

use Magento\Framework\Validator\ValidatorInterface;

interface ValidatorCredoCampaignInterface extends ValidatorInterface
{
    const ERROR_IS_EMPTY_CAMPAIGN_ID  = 'La columna campaign_id esta vacia';
    const ERROR_IS_EMPTY_SKU = 'La columna sku esta vacia';
    const ERROR_IS_EMPTY_FEE = 'La columna fee esta vacia';
    const ERROR_IS_EMPTY_MAX_UNITS = 'La columna max_units esta vacia';
    const ERROR_IS_EMPTY_STATUS    = 'La columna status esta vacia';
    const ERROR_FORMAT_STATUS = 'El valor de la columna status es diferente de 1: Habilitado o 0: Deshabilitado';
}
