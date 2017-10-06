<?php
/**
 * Copyright © 2017 SalesIgniter. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 */

namespace SalesIgniter\Debugger\Framework;

use Symfony\Component\Console;
use Magento\Framework\App\Filesystem\DirectoryList;

class Cli extends \Magento\Framework\Console\Cli
{
    /**
     * @var \Whoops\Run
     */
    private $run;

    public function onShutdown()
    {
        $request = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\App\RequestInterface');
        if ($request->getPathInfo() !== '/salesigniter_debugger/showlogsnav/index/') {
            $helperDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
            $this->getDataAsHtmlToFile($helperDebugger);
        }
        $lasterror = error_get_last();
        if (in_array($lasterror['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE])) {
            $this->writeToLogFile(new \ErrorException($lasterror['message'], $lasterror['type'], 1, $lasterror['file'], $lasterror['line']));
        }
    }

    public function getDataAsHtmlToFile($helperDebugger)
    {
        if ($helperDebugger->isEnabled()) {
            if ($helperDebugger->getSession()->getDebuggerData()) {
                $debuggerData = unserialize($helperDebugger->getSession()->getDebuggerData());
                foreach ($debuggerData as $context => $content) {
                    $html = '';
                    foreach ($content as $count => $value) {
                        $html .= '<div>'.$value.'</div>';
                    }
                    $this->writeOnlyToLogFile($html, $context);
                }

                return $html;
            }
        }

        return '';
    }

    /**
     * @param \Exception $exception
     *
     * @throws \InvalidArgumentException
     */
    public function writeToLogFile(\Exception $exception)
    {
        if (class_exists('\SalesIgniter\Debugger\Helper\Data') && \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data')->isEnabled()) {
            $this->run = new \Whoops\Run();
            $handler = new \Whoops\Handler\PrettyPageHandler();
            $handler->setEditor(function ($file, $line) {
                return 'editor://open/?file=%file&line=%line';
            });
            $this->run->pushHandler($handler);
            $this->run->writeToOutput(false);
            $this->run->allowQuit(false);
            $returnMessage = $this->run->handleException($exception);
            /** @var \SalesIgniter\Debugger\Helper\Data $helperDebugger */
            $helperDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
            if (strpos($exception->getMessage(), 'isDebug_1') === false) {
                $helperDebugger->dfFileWrite(DirectoryList::MEDIA, 'sidebugger1/logs/errors/'.date('Y-m-d-H-i').'-'.time().'.html', $returnMessage);
            } else {
                $exArr = explode('__', $exception->getMessage());
                $context = '';
                if (isset($exArr[1])) {
                    $contextArr = explode('_', $exArr[1]);
                    $context = $contextArr[1].'/';
                }
                $helperDebugger->dfFileWrite(DirectoryList::MEDIA, 'sidebugger1/logs/debug/'.$context.date('Y-m-d-H-i').'-'.time().'.html', $returnMessage);
            }
        }
    }

    /**
     * @param        $message
     * @param string $context
     */
    public function writeOnlyToLogFile($message, $context = '')
    {
        if (class_exists('\SalesIgniter\Debugger\Helper\Data') && \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data')->isEnabled()) {
            /** @var \SalesIgniter\Debugger\Helper\Data $helperDebugger */
            $helperDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
            $helperDebugger->dfFileWrite(DirectoryList::MEDIA, 'sidebugger1/logs/debug/'.$context.'.html', $message, 1);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception The exception in case of unexpected error
     */
    public function doRun(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        register_shutdown_function([&$this, 'onShutdown']);
        $exitCode = parent::doRun($input, $output);

        return $exitCode;
    }
}
