<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Controller;

abstract class AbstractOauth extends \Magento\Framework\App\Action\Action
{

    protected function _windowClose()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(json_encode([
                'windowClose' => true
            ]));
        } else {
            $this->getResponse()->setBody('<script type="text/javascript">window.close();</script>');
        }
    }
}
