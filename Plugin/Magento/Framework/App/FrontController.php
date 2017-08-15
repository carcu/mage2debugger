<?php

namespace SalesIgniter\Debugger\Plugin\Magento\Framework\App;

class FrontController
{
    //function beforeMETHOD($subject, $arg1, $arg2){}
    //function aroundMETHOD($subject, $procede, $arg1, $arg2){return $proceed($arg1, $arg2);}
    //function afterMETHOD($subject, $result){return $result;}
    /**
     * Set current store for admin area
     *
     * @param \Magento\Framework\App\FrontController  $subject
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(
        \Magento\Framework\App\FrontController $subject,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!\Whoops\Util\Misc::isAjaxRequest()) {
            //\Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data')->resetData();
        }
    }
}
