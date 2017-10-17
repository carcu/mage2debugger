<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 */

namespace SalesIgniter\Debugger\Plugin\Magento\Framework\Message;

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
        \Magento\Framework\Message\MessageInterface $message,
        $group = null
    ) {
        if (class_exists('\SalesIgniter\Debugger\Helper\Data')) {
            $myDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
            $myDebugger->addDataWithTrace($message, 'messages');
        }
        $returnValue = $proceed($message, $group);

        return $returnValue;
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
    public function aroundAddError(
        \Magento\Framework\Message\Manager $subject,
        \Closure $proceed,
        $message,
        $group = null
    ) {
        if (class_exists('\SalesIgniter\Debugger\Helper\Data')) {
            $myDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
            $myDebugger->addDataWithTrace($message, 'exceptions');
        }
        $returnValue = $proceed($message, $group);

        return $returnValue;
    }

    /**
     * Adds new error message.
     *
     * @param string      $message
     * @param string|null $group
     *
     * @return ManagerInterface
     */
    public function addErrorMessage(\Magento\Framework\Message\Manager $subject,
                                    \Closure $proceed,
                                    $message, $group = null)
    {
        if (class_exists('\SalesIgniter\Debugger\Helper\Data')) {
            $myDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
            $myDebugger->addDataWithTrace($message, 'adderror');
        }
        $returnValue = $proceed($message, $group);

        return $returnValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Exception $exception
     * @param string     $alternativeText
     * @param string     $group
     *
     * @return $this
     */
    public function addExceptionMessage(\Magento\Framework\Message\Manager $subject,
                                        \Closure $proceed,
                                        \Exception $exception, $alternativeText = null, $group = null)
    {
        if (class_exists('\SalesIgniter\Debugger\Helper\Data')) {
            $myDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
            $myDebugger->addDataWithTrace($exception, 'exceptions');
        }
        $returnValue = $proceed($exception, $alternativeText, $group);

        return $returnValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Exception $exception
     * @param string     $alternativeText
     * @param string     $group
     *
     * @return $this
     */
    public function addException(\Magento\Framework\Message\Manager $subject,
                                 \Closure $proceed,
                                 \Exception $exception, $alternativeText = null, $group = null)
    {
        if (class_exists('\SalesIgniter\Debugger\Helper\Data')) {
            $myDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
            $myDebugger->addDataWithTrace($exception, 'exceptions');
        }
        $returnValue = $proceed($exception, $alternativeText, $group);

        return $returnValue;
    }
}
