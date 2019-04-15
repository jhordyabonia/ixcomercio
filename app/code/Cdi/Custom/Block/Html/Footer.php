<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cdi\Custom\Block\Html;

/**
 * Html page footer block
 *
 * @api
 * @since 100.0.2
 */
class Footer extends \Magento\Theme\Block\Html\Footer
{
	
	private $_phone = null;
	
	public function getStoreConfig($key)
    {
		$this->_value = $this->_scopeConfig->getValue(
			$key,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
        return __($this->_value);
    }
}
