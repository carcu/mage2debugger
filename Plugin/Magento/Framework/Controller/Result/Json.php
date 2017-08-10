<?php

namespace SalesIgniter\Debugger\Plugin\Magento\Framework\Controller\Result;

class Json
{
    public function beforeSetData(
        \Magento\Framework\Controller\Result\Json $subject,
        $data, $cycleCheck = false, $options = []
    ) {
        $data['debuggerData'] = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data')->getDataAsHtml();
        return [$data];
    }

//function aroundMETHOD($subject, $procede, $arg1, $arg2){return $proceed($arg1, $arg2);}
//function afterMETHOD($subject, $result){return $result;}
}
