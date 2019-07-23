<?php
/**
 * Pasarela_Bancomer payment method model
 *
 * @category    Pasarela
 * @package     Pasarela_Bancomer
 * @author      Valentina Aguirre
 * @license     http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0
 */

namespace Pasarela\Bancomer\Model;

class Bancomer extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'pasarela_bancomer';

    protected $_code = self::CODE;

    protected $_isOffline = true;
}