<?php
/**
 * Payment CC Types Source Model
 *
 * @category    Credomatic_Visa
 * @package     Credomatic_Visa
 * @author      Intcomex
 * @copyright   Intcomex (https://www.intcomex.com/)
 */

namespace Intcomex\Credomatic_Visa\Model\Source;

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
