<?php

namespace SalesIgniter\Debugger\Plugin;

/**
 * Plugin to add Toolbar to the Response add the
 * end of the body
 */
class SessionPlugin
{

    /*public function afterStart(\Magento\Framework\Session\SessionManager $session, $result)
    {

    }

    public function beforeWriteClose(\Magento\Framework\Session\SessionManager $session)
    {

    }*/
    /*public function aroundClearStorage(\Magento\Framework\Session\SessionManager $session,
                                       \Closure $proceed)
    {
        if (!isset($_SESSION['PARSE_ERROR'])) {
            return $proceed();
        } else {
            return $session;
        }
    }*/

    /*public function beforeDestroy(\Magento\Framework\Session\SessionManager $session, array $options = null)
    {
        if (isset($_SESSION['PARSE_ERROR'])) {
            $options['clear_storage'] = false;
        }
        return [$options];
    }*/
}
