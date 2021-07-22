<?php
/**
 * Payment CC Types Source Model
 *
 * @category    Pagalo_Visa
 * @package     Pagalo_Visa
 * @author      Pagalo_Visa
 * @copyright   Pagalo_Visa (https://www.pagalo.com/)
 */

namespace Magento\Pagalo_Visa\Model\Source;

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
