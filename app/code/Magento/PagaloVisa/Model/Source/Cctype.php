<?php
/**
 * Payment CC Types Source Model
 *
 * @category    PagaloVisa
 * @package     PagaloVisa
 * @author      PagaloVisa
 * @copyright   PagaloVisa (https://www.pagalo.com/)
 */

namespace Magento\PagaloVisa\Model\Source;

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
