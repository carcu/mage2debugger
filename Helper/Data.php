<?php
/**
 * Copyright © 2015 CedCommerce. All rights reserved.
 */

namespace SalesIgniter\Debugger\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $filesystem;

    protected $coreRegistry;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $backendSession;
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    private $isEnabled;
    /**
     * @var \Magento\Framework\App\Http
     */
    private $http;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Backend\App\ConfigInterface       $backendConfig
     * @param \Magento\Catalog\Model\Session             $catalogSession
     * @param \Magento\Framework\App\Http                $http
     * @param \Magento\Backend\Model\Session             $backendSession
     * @param \Magento\Framework\App\State               $appState
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem              $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\Catalog\Model\Session $catalogSession,
        \SalesIgniter\Debugger\Framework\Http $http,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
        parent::__construct($context);
        $this->catalogSession = $catalogSession;
        $this->storeManager = $storeManager;
        $this->backendSession = $backendSession;
        $this->appState = $appState;
        $this->isEnabled = true;
        $this->http = $http;
    }

    public function deleteFiles($directory, $type = 0)
    {
        /** @var \Magento\Framework\App\ObjectManager $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $om->get('Magento\Framework\Filesystem');
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface|\Magento\Framework\Filesystem\Directory\Write $writer */
        $writer = $filesystem->getDirectoryWrite($directory);
        if ($type === 0) {
            $writer->delete('sidebugger1/logs/debug');
        } else {
            $writer->delete('sidebugger1/logs/errors');
        }
    }

    public function dfFileWrite($directory, $relativeFileName, $contents, $modeType = 0)
    {
        /** @var \Magento\Framework\App\ObjectManager $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $om->get('Magento\Framework\Filesystem');
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface|\Magento\Framework\Filesystem\Directory\Write $writer */
        $writer = $filesystem->getDirectoryWrite($directory);
        $mode = 'w';
        if ($modeType === 1) {
            $mode = 'a+';
        }
        /** @var \Magento\Framework\Filesystem\File\WriteInterface|\Magento\Framework\Filesystem\File\Write $file */
        $file = $writer->openFile($relativeFileName, $mode);
        try {
            $file->lock();
            try {
                $file->write($contents);
            } finally {
                $file->unlock();
            }
        } finally {
            $file->close();
        }

        return $this->storeManager->getStore()->getBaseUrl().$filesystem->getUri($directory).'/'.$relativeFileName;
    }

    public function isEnabled()
    {
        return $this->isEnabled && $this->getSession()->isSessionExists() && $this->getSession()->getSessionId();
    }

    public function isEnabledForAjax()
    {
        return $this->isEnabled && false;
    }

    /**
     * Returns true if current scope is backend.
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isBackend()
    {
        return $this->appState->getAreaCode() === 'adminhtml';
    }

    public function getSession()
    {
        if ($this->isBackend()) {
            return $this->backendSession;
        }

        return $this->catalogSession;
    }

    public function getDataAsHtmlToFile()
    {
        if ($this->isEnabled()) {
            if ($this->getSession()->getDebuggerData()) {
                $debuggerData = unserialize($this->getSession()->getDebuggerData());

                foreach ($debuggerData as $context => $content) {
                    $html = '';
                    foreach ($content as $count => $value) {
                        $html .= '<div>'.$value.'</div>';
                    }
                    $this->http->writeOnlyToLogFile($html, $context);
                }

                return $html;
            }
        }

        return '';
    }

    public function addDataWithTrace($var, $context = null)
    {
        if ($var !== null && $this->isEnabled()) {
            if ($var instanceof \Exception) {
                $this->http->writeToLogFile($var);
            } else {
                try {
                    $functionName = 'isDebug_1__isContext_'.$context.'_a';
                    $$functionName->variable($var);
                    //$functionName([$var]);
                    //$var->method();
                } catch (\Exception $exception) {
                    $this->http->writeToLogFile($exception);
                }
            }
        }
    }

    public function resetDataFiles($type = 0)
    {
        $this->deleteFiles(DirectoryList::MEDIA, $type);
    }

    public function generateDebugReport($definedVars)
    {
        $ignoreList = ['HTTP_POST_VARS', 'HTTP_GET_VARS',
            'HTTP_COOKIE_VARS', 'HTTP_SERVER_VARS',
            'HTTP_ENV_VARS', 'HTTP_SESSION_VARS',
            '_ENV', 'PHPSESSID', 'SESS_DBUSER',
            'SESS_DBPASS', 'HTTP_COOKIE', 'myDebugger', 'this', ];

        $realDefinedVars = array_keys($definedVars);

        return array_diff($realDefinedVars, $ignoreList);
    }

    public function addData($var, $context = 'general', $depth = 6, $depthInside = 6)
    {
        if ($this->isEnabled()) {
            $debuggerData = [];
            if ($this->getSession()->getDebuggerData()) {
                $debuggerData = unserialize($this->getSession()->getDebuggerData());
            }

            list($file, $line, $code) = $this->findLocation();
            if ($context === null) {
                $context = 'general';
            }
            //$templateHelper = new \Whoops\Util\TemplateHelper();
            $debuggerData[$context][] = 'File: '.$file.':'.$line.'<br/> Time:'.date('Y-m-d H:i:s').'<br/>'.dump_r($var, true, true, $depth, $depthInside);
            $this->getSession()->setDebuggerData(serialize($debuggerData));
        }
    }

    public function pDump($var)
    {
        if ($this->isEnabled()) {
            $_GET['pData'] = $var;
            throw new \ErrorException('some error');
        }
    }

    public function sDump($var)
    {
        if ($this->isEnabled()) {
            $templateHelper = new \Whoops\Util\TemplateHelper();
            $this->http->writeOnlyToLogFile($templateHelper->dump($var), 'old');
        }
    }

    public function getDataAsHtml()
    {
        if ($this->isEnabled()) {
            if ($this->getSession()->getDebuggerData()) {
                $debuggerData = unserialize($this->getSession()->getDebuggerData());

                $html = '';
                foreach ($debuggerData as $context => $content) {
                    //array_reverse($content);
                    $html .= '<h3>'.$context.'</h3>';
                    if (count($content) > 1) {
                        $html .= '<div class="debuggerAccordionsSub">';
                    }
                    foreach ($content as $count => $value) {
                        if (count($content) > 1) {
                            $html .= '<h3>Debug: '.$count.'</h3>';
                        }
                        $html .= '<div>'.$value.'</div>';
                    }
                    if (count($content) > 1) {
                        $html .= '</div>';
                    }
                }

                return $html;
            }
        }

        return '';
    }

    /**
     * Because in version 2.2 al the serializer and json decodes have been replace by one function.
     *
     * @param $data
     *
     * @return bool|string
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function serialize($data, $forceDecode = false)
    {
        if (class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            $serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\Serializer\Json::class);

            return $serializer->serialize($data);
        } else {
            if ($forceDecode || $this->versionIs22AndOver()) {
                return json_encode($data);
            } else {
                return serialize($data);
            }
        }
    }

    /**
     * Because in version 2.2 al the serializer and json decodes have been replace by one function.
     *
     * @param $data
     *
     * @return array|bool|float|int|mixed|null|string
     *
     * @throws \InvalidArgumentException
     */
    public function unserialize($data)
    {
        if (class_exists('Magento\Framework\Serialize\Serializer\Json')) {
            $serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\Serializer\Json::class);

            return $serializer->unserialize($data);
        } else {
            if ($this->versionIs22AndOver()) {
                return json_decode($data);
            } else {
                return unserialize($data);
            }
        }
    }

    public function resetData()
    {
        if ($this->isEnabled() /*&& $this->getSession()->isSessionExists() && $this->getSession()->getSessionId() && $this->getSession()->getDebugerData()*/) {
            $this->getSession()->unsDebuggerData();
        }
    }

    public static function printRLevel($data, $level = 5)
    {
        static $innerLevel = 1;
        static $tabLevel = 1;
        $type = gettype($data);
        $tabs = str_repeat('    ', $tabLevel);
        $quoteTabes = str_repeat('    ', $tabLevel - 1);
        $output = '';
        $elements = [];
        $recursiveType = ['object', 'array'];
        // Recursive
        if (in_array($type, $recursiveType)) {
            // If type is object, try to get properties by Reflection.
            if ($type == 'object') {
                $output = get_class($data).' '.ucfirst($type);
                $ref = new \ReflectionObject($data);
                $properties = $ref->getProperties();
                foreach ($properties as $property) {
                    $property->setAccessible(true);
                    $pType = $property->getName();
                    if ($property->isProtected()) {
                        $pType .= ':protected';
                    } elseif ($property->isPrivate()) {
                        $pType .= ':'.$property->class.':private';
                    }
                    if ($property->isStatic()) {
                        $pType .= ':static';
                    }
                    $elements[$pType] = $property->getValue($data);
                }
            } // If type is array, just retun it's value.
            elseif ($type == 'array') {
                $output = ucfirst($type);
                $elements = $data;
            }
            // Start dumping data
            if ($level == 0 || $innerLevel < $level) {
                // Start recursive print
                $output .= "\n{$quoteTabes}(";
                foreach ($elements as $key => $element) {
                    $output .= "\n{$tabs}[{$key}] => ";
                    // Increment level
                    $tabLevel = $tabLevel + 2;
                    ++$innerLevel;
                    $output .= in_array(gettype($element), $recursiveType) ? self::printRLevel($element, $level) : $element;
                    // Decrement level
                    $tabLevel = $tabLevel - 2;
                    --$innerLevel;
                }
                $output .= "\n{$quoteTabes})\n";
            } else {
                $output .= "\n{$quoteTabes}*MAX LEVEL*\n";
            }
        } else {
            $output = $data;
        }

        return $output;
    }

    /**
     * Finds the location where dump was called.
     *
     * @return array [file, line, code]
     */
    private function findLocation()
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $item) {
            if (isset($item['class']) && $item['class'] === __CLASS__) {
                $location = $item;
                continue;
            } elseif (isset($item['function'])) {
                try {
                    $reflection = isset($item['class'])
                        ? new \ReflectionMethod($item['class'], $item['function'])
                        : new \ReflectionFunction($item['function']);
                    if ($reflection->isInternal() || preg_match('#\s@tracySkipLocation\s#', (string) $reflection->getDocComment())) {
                        $location = $item;
                        continue;
                    }
                } catch (\ReflectionException $e) {
                }
            }
            break;
        }

        if (isset($location['file'], $location['line']) && is_file($location['file'])) {
            $lines = file($location['file']);
            $line = $lines[$location['line'] - 1];

            return [
                $location['file'],
                $location['line'],
                trim(preg_match('#\w*dump(er::\w+)?\(.*\)#i', $line, $m) ? $m[0] : $line),
            ];
        }
    }
}
