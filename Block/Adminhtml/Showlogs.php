<?php
/**
 * Showlogs.
 *
 * @copyright Copyright © 2017 SalesIgniter. All rights reserved
 * @author    cristian@itwebexperts.com
 */

namespace SalesIgniter\Debugger\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

class Showlogs extends Template
{
    /**
     * @var string
     */
    protected $_template = 'SalesIgniter_Debugger::showlogs.phtml';

    public function getNavUrl()
    {
        return $this->getUrl('salesigniter_debugger/showlogs/index');
    }
    // write your methods here...
}
