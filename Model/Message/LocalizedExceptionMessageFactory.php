<?php
/**
 * Copyright © 2017 SalesIgniter. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 */

namespace SalesIgniter\Debugger\Model\Message;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ExceptionMessageFactoryInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Factory;
use Magento\Framework\Exception\RuntimeException;

class LocalizedExceptionMessageFactory implements ExceptionMessageFactoryInterface
{
    /**
     * @var \Magento\Framework\Message\Factory
     */
    private $messageFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Factory                         $messageFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(Factory $messageFactory, \Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->messageFactory = $messageFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function createMessage(\Exception $exception, $type = MessageInterface::TYPE_ERROR)
    {
        if ($exception instanceof LocalizedException) {
            try {
                //@debug
                if (class_exists('\SalesIgniter\Debugger\Helper\Data')) {
                    $myDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
                    $myDebugger->addDataWithTrace($exception);
                }
            } catch (\Exception $e) {
            }
            //@end-debug
            return $this->messageFactory->create($type)
                ->setText($exception->getMessage());
        }
        throw new RuntimeException(
            __('Exception instance doesn\'t match %1 type', UrlAlreadyExistsException::class)
        );
    }
}
