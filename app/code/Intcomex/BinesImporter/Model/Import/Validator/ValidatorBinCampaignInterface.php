<?php
declare(strict_types=1);

namespace Intcomex\BinesImporter\Model\Import\Validator;

use Magento\Framework\Validator\ValidatorInterface;

interface ValidatorBinCampaignInterface extends ValidatorInterface
{
    const ERROR_IS_EMPTY_CAMPAIGN  = 'La columna campaign esta vacia';
    const ERROR_IS_EMPTY_BIN_CODES = 'La columna bin_codes esta vacia';
    const ERROR_IS_EMPTY_STATUS    = 'La columna status esta vacia';
    const ERROR_FORMAT_STATUS      = 'El valor de la columna status es diferente de 1: Habilitado o 0: Deshabilitado';
}
