<?php
/**
 * Index.
 *
 * @copyright Copyright © 2017 SalesIgniter. All rights reserved
 * @author    cristian@itwebexperts.com
 */

namespace SalesIgniter\Debugger\Controller\Showlogs;

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
        $helperDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
        if ($this->getRequest()->getParam('delete')) {
            $helperDebugger->resetData();
            $helperDebugger->resetDataFiles();
        }elseif ($this->getRequest()->getParam('deleteerror')) {
        $helperDebugger->resetDataFiles(1);
    } else {
            $helperDebugger->getDataAsHtmlToFile();
            $helperDebugger->resetData();
        }

        return $resultPage;
    }
}
