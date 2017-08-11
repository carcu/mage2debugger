<?php

namespace SalesIgniter\Debugger\Plugin\Magento\Framework\Controller\Result;

class Json
{
    private function arrayIsAssoc(array $array)
    {
        /*$i = 0;
        foreach ($a as $k => $v) {
            if ($k !== $i++) {
                return true;
            }
        }
        return false;*/
        return (array_values($array) !== $array);
    }

    public function beforeSetData(
        \Magento\Framework\Controller\Result\Json $subject,
        $data, $cycleCheck = false, $options = []
    ) {
        if (\Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data')->isEnabled()) {
            $debuggerHtml = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data')->getDataAsHtml();
            if ($debuggerHtml !== '') {
                if ($this->arrayIsAssoc($data)) {
                    $data['debuggerData'] = $debuggerHtml;
                } else {
                    $data[]['debuggerData'] = $debuggerHtml;
                }
            }
        }
        return [$data];
    }

//function aroundMETHOD($subject, $procede, $arg1, $arg2){return $proceed($arg1, $arg2);}
//function afterMETHOD($subject, $result){return $result;}
}
