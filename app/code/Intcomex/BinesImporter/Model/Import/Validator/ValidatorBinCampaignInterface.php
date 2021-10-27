<?php
declare(strict_types=1);

namespace Intcomex\BinesImporter\Model\Import\Validator;

use Magento\Framework\Validator\ValidatorInterface;

interface ValidatorBinCampaignInterface extends ValidatorInterface
{
	const ERROR_IS_EMPTY_BIN_CODE = 'La columna bin_code esta vacia';
	const ERROR_IS_EMPTY_STATUS   = 'La columna status esta vacia';
    const ERROR_FORMAT_BIN_CODE   = 'El valor de la columna bin_code no cumple con el formato de %1$d dígitos';
    const ERROR_FORMAT_STATUS     = 'El valor de la columna status es diferente de 1: Habilitado o 0: Deshabilitado';
}
