<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Block\Footer;

use Wagento\Zendesk\Helper\Data;

class ZendeskWidget extends \Magento\Theme\Block\Html\Footer
{

    /**
     * @return mixed
     */
    public function getSubdomain()
    {
        return $this->_scopeConfig->getValue(Data::PATH_SUBDOMAIN);
    }

    /**
     * @return bool
     */
    public function showWidget()
    {
        return $this->_scopeConfig->getValue('zendesk/help_center/include_web_widget');
    }
}
