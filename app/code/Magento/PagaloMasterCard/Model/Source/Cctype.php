<?php
/**
 * Payment CC Types Source Model
 *
 * @category    PagaloMasterCard
 * @package     PagaloMasterCard
 * @author      PagaloMasterCard
 * @copyright   PagaloMasterCard (https://www.pagalo.com/)
 */

namespace Magento\PagaloMasterCard\Model\Source;

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
