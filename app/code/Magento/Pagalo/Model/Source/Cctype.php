<?php
/**
 * Payment CC Types Source Model
 *
 * @category    Pagalo
 * @package     Pagalo
 * @author      Pagalo
 * @copyright   Pagalo (https://www.pagalo.com/)
 */

namespace Magento\Pagalo\Model\Source;

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
