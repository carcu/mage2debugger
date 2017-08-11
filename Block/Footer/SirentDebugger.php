<?php

namespace SalesIgniter\Debugger\Block\Footer;

class SirentDebugger extends \Magento\Framework\View\Element\Template
{
    protected $_template = "SalesIgniter_Debugger::footer/sirentdebugger.phtml";

    public function getRedirectUrl()
    {
        return $this->getUrl(
            'salesigniter_debugger/redirectionexception',
            [
                '_secure' => $this->getRequest()->isSecure(),
            ]
        );
    }

}
