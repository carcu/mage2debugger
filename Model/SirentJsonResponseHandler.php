<?php

namespace SalesIgniter\Debugger\Model;

use Whoops\Exception\Formatter;

/***
 * Class SirentJsonResponseHandler
 *
 *
 * Not used
 *
 * @package SalesIgniter\Debugger\Model
 */
class SirentJsonResponseHandler extends \Whoops\Handler\Handler
{
    /**
     * @var bool
     */
    private $returnFrames = false;

    /**
     * @var bool
     */
    private $jsonApi = false;
    /**
     * @var \SalesIgniter\Debugger\Helper\Data
     */
    private $debuggerHelper;

    /**
     * SirentJsonResponseHandler constructor.
     *
     * @param \SalesIgniter\Debugger\Helper\Data $debuggerHelper
     */
    /*public function __construct(
         \SalesIgniter\Debugger\Helper\Data $debuggerHelper
     ) {
         $this->debuggerHelper = $debuggerHelper;
     }*/

    /**
     * Returns errors[[]] instead of error[] to be in compliance with the json:api spec
     *
     * @param bool $jsonApi Default is false
     *
     * @return $this
     */
    public function setJsonApi($jsonApi = false)
    {
        $this->jsonApi = (bool)$jsonApi;
        return $this;
    }

    /**
     * @param  bool|null $returnFrames
     *
     * @return bool|$this
     */
    public function addTraceToOutput($returnFrames = null)
    {
        if (func_num_args() == 0) {
            return $this->returnFrames;
        }

        $this->returnFrames = (bool)$returnFrames;
        return $this;
    }

    /**
     * @return int
     */
    public function handle()
    {
        if ($this->jsonApi === true) {
            $response = [
                'errors' => [
                    Formatter::formatExceptionAsDataArray(
                        $this->getInspector(),
                        $this->addTraceToOutput()
                    ),
                ]
            ];
        } else {
            $response = [
                'error' => Formatter::formatExceptionAsDataArray(
                    $this->getInspector(),
                    $this->addTraceToOutput()
                ),
            ];
        }

        //echo json_encode($response, defined('JSON_PARTIAL_OUTPUT_ON_ERROR') ? JSON_PARTIAL_OUTPUT_ON_ERROR : 0);
        //$this->debuggerHelper->debug('Start Product Data:');

        Debugger::getFireLogger()->maxDepth = 6;
        Debugger::getFireLogger()->maxLength = 3000;
        Debugger::fireLog($response);

        return \Whoops\Handler\Handler::QUIT;
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'application/json';
    }
}
