<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 */

namespace SalesIgniter\Debugger\Plugin\Magento\Framework\Message;

use Magento\Framework\Message\MessageInterface;

class Manager
{
    /**
     * @var \SalesIgniter\Debugger\Helper\Data
     */
    protected $helperDebugger;

    /**
     * @param \SalesIgniter\Debugger\Helper\Data $helperDebugger
     */
    public function __construct(
        \SalesIgniter\Debugger\Helper\Data $helperDebugger
    ) {
        $this->helperDebugger = $helperDebugger;
    }

    /**
     * Return product base price.
     *
     * @param \Magento\Framework\Message\Manager          $subject
     * @param \Closure                                    $proceed
     * @param \Magento\Framework\Message\MessageInterface $message
     * @param null                                        $group
     *
     * @return float
     *
     * @internal param \Magento\Catalog\Model\Product $product
     */
    public function aroundAddMessage(
        \Magento\Framework\Message\Manager $subject,
        \Closure $proceed,
        MessageInterface $message,
        $group = null
    ) {
        if (class_exists('\SalesIgniter\Debugger\Helper\Data')) {
            $myDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
            $myDebugger->addDataWithTrace($message, 'aroundaddMessage');
        }
        $returnValue = $proceed($message, $group);

        return $returnValue;
    }
}
