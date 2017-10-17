<?php
/**
 * Copyright (c) 2017 Sales Igniter. All Rights Reserved.
 * Written by Cristian Arcu <cristian@itwebexperts.com>.
 */

namespace SalesIgniter\Debugger\Controller\Adminhtml\Showlogs;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $pageFactory
     */
    public function __construct(Context $context, PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * Index Action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->pageFactory->create();
        /** @var \SalesIgniter\Debugger\Helper\Data $helperDebugger */
        $helperDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
        if ($this->getRequest()->getParam('delete')) {
            $helperDebugger->resetData();
            $helperDebugger->resetDataFiles();
        } elseif ($this->getRequest()->getParam('deleteerror')) {
            $helperDebugger->resetDataFiles(1);
        } else {
            $helperDebugger->getDataAsHtmlToFile();
            $helperDebugger->resetData();
        }
        echo 'here is reseted';
        die();

        return $resultPage;
    }
}
