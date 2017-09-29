<?php
/**
 * Showlogs
 *
 * @copyright Copyright © 2017 SalesIgniter. All rights reserved.
 * @author    cristian@itwebexperts.com
 */

namespace SalesIgniter\Debugger\Block;

use Magento\Framework\View\Element\Template;

class Showlogs extends Template
{
    /**
     * @var string $_template
     */
    protected $_template = "showlogs.phtml";

    public function getNavUrl()
    {
        return $this->getUrl('salesigniter_debugger/showlogsnav/index');
    }
    // write your methods here...
}
