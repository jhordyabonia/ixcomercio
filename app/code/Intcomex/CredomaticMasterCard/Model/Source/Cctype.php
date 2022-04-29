<?php
/**
 * Payment CC Types Source Model
 *
 * @category    CredomaticMasterCard
 * @package     CredomaticMasterCard
 * @author      Intcomex
 * @copyright   Intcomex (https://www.intcomex.com/)
 */

namespace Intcomex\CredomaticMasterCard\Model\Source;

class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {	//, 'AE', 'DI', 'JCB', 'OT'
        return array('MC');
    }
    
}
