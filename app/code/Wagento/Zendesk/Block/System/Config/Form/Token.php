<?php
/**
 * Created by PhpStorm.
 * User: francisca
 * Date: 2/20/18
 * Time: 4:50 PM
 */

namespace Wagento\Zendesk\Block\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Token extends Field
{

    /**
     * AToken constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return empty($element->getValue()) ? '' : '******';
    }
}
