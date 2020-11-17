<?php
/**
 * Payment CC Types Source Model
 *
 * @category    Credomatic
 * @package     Credomatic
 * @author      Intcomex
 * @copyright   Intcomex (https://www.intcomex.com/)
 */

namespace Intcomex\Credomatic\Model\Source;

class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {	//, 'AE', 'DI', 'JCB', 'OT'
        return array('VI', 'MC');
    }
    
}
