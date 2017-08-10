<?php
/**
 * Copyright © 2015 CedCommerce. All rights reserved.
 */

namespace SalesIgniter\Debugger\Helper;

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
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Backend\App\ConfigInterface       $backendConfig
     * @param \Magento\Catalog\Model\Session             $catalogSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem              $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
        parent::__construct($context);
        $this->catalogSession = $catalogSession;
        $this->storeManager = $storeManager;
    }

    public function dfFileWrite($directory, $relativeFileName, $contents)
    {
        /** @var \Magento\Framework\App\ObjectManager $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $om->get('Magento\Framework\Filesystem');
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface|\Magento\Framework\Filesystem\Directory\Write $writer */
        $writer = $filesystem->getDirectoryWrite($directory);
        /** @var \Magento\Framework\Filesystem\File\WriteInterface|\Magento\Framework\Filesystem\File\Write $file */
        $file = $writer->openFile($relativeFileName, 'w');
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
        return $this->storeManager->getStore()->getBaseUrl() . $filesystem->getUri($directory) . '/' . $relativeFileName;
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
                $output = get_class($data) . ' ' . ucfirst($type);
                $ref = new \ReflectionObject($data);
                $properties = $ref->getProperties();
                foreach ($properties as $property) {
                    $property->setAccessible(true);
                    $pType = $property->getName();
                    if ($property->isProtected()) {
                        $pType .= ":protected";
                    } elseif ($property->isPrivate()) {
                        $pType .= ":" . $property->class . ":private";
                    }
                    if ($property->isStatic()) {
                        $pType .= ":static";
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
                    $innerLevel++;
                    $output .= in_array(gettype($element), $recursiveType) ? self::printRLevel($element, $level) : $element;
                    // Decrement level
                    $tabLevel = $tabLevel - 2;
                    $innerLevel--;
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

    public function var2console($var, $name = '', $now = false)
    {
        return self::printRLevel($var);
        /* if (strlen($name)) {
             $this->str2console("$type $name = " . $this->printRLevel($var) . ';', $now);
         } else {
             $this->str2console("$type = " . $this->printRLevel($var) . ';', $now);
         }*/
    }

    public static function toConsole($var)
    {
        if ($var === null) {
            $type = 'NULL';
        } elseif (is_bool($var)) {
            $type = 'BOOL';
        } elseif (is_string($var)) {
            $type = 'STRING[' . strlen($var) . ']';
        } elseif (is_int($var)) {
            $type = 'INT';
        } elseif (is_float($var)) {
            $type = 'FLOAT';
        } elseif (is_array($var)) {
            $type = 'ARRAY[' . count($var) . ']';
        } elseif (is_object($var)) {
            $type = 'OBJECT';
        } elseif (is_resource($var)) {
            $type = 'RESOURCE';
        } else {
            $type = '???';
        }
        $str = "$type = " . self::printRLevel($var) . ';';
        $string = "<script type='text/javascript'>\n";
        $string .= "//<![CDATA[\n";
        $string .= "console.log(" . json_encode($str) . ");\n";
        $string .= "//]]>\n";
        $string .= "</script>";
        return $string;
    }

    public function str2console($str, $now = false)
    {
        if ($now) {
            return $str;
        } else {
            return self::toConsole($str);
        }
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
                    if ($reflection->isInternal() || preg_match('#\s@tracySkipLocation\s#', (string)$reflection->getDocComment())) {
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

    public function addData($var, $context = null)
    {
        //$result = $this->var2console($var, true);
        $debuggerData = [];
        if ($this->catalogSession->getDebuggerData()) {
            $debuggerData = unserialize($this->catalogSession->getDebuggerData());
        }

        list($file, $line, $code) = $this->findLocation();
        if ($context === null) {
            $context = 'Debug: ' . count($debuggerData) . ' File: ' . $file;
        }
        $templateHelper = new \Whoops\Util\TemplateHelper();
        $debuggerData[$context][] = $templateHelper->dump($var);
        $this->catalogSession->setDebuggerData(serialize($debuggerData));
    }

    public function getDataAsHtml()
    {
        if ($this->catalogSession->getDebuggerData()) {
            $debuggerData = unserialize($this->catalogSession->getDebuggerData());

            $html = '';
            foreach ($debuggerData as $context => $content) {
                //array_reverse($content);
                $html .= '<h3>' . $context . '</h3>';
                if (count($content) > 1) {
                    $html .= '<div class="debuggerAccordionsSub">';
                }
                foreach ($content as $count => $value) {
                    if (count($content) > 1) {
                        $html .= '<h3>Debug: ' . $count . '</h3>';
                    }
                    $html .= '<div>' . $value . '</div>';
                }
                if (count($content) > 1) {
                    $html .= '</div>';
                }
            }
            return $html;
        }
        return '';
    }

    public function resetData()
    {
        $this->catalogSession->unsDebuggerData();
    }
}
