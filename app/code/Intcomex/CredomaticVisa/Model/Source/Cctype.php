<?php
/**
 * Payment CC Types Source Model
 *
 * @category    CredomaticVisa
 * @package     CredomaticVisa
 * @author      Intcomex
 * @copyright   Intcomex (https://www.intcomex.com/)
 */

namespace Intcomex\CredomaticVisa\Model\Source;

class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {	//, 'AE', 'DI', 'JCB', 'OT'
        return array('VI');
    }
    
}
