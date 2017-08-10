<?php

namespace SalesIgniter\Debugger\Observer;

use Magento\Framework\App\RequestInterface;

/**
 */
class ObserverHttp implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * ObserverHttp constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //bdump('ddds');
    }
}
