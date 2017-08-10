<?php

namespace SalesIgniter\Debugger\Framework;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Plugin to add Toolbar to the Response add the
 * end of the body
 */
class ResponsePlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\Session          $catalogSession
     */
    public function __construct(RequestInterface $request,
                                \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->request = $request;
        $this->catalogSession = $catalogSession;
    }

    /**
     * Add our toolbar to the response
     *
     * @param ResponseInterface $response
     */
    public function beforeSendResponse(ResponseInterface $response)
    {
        $debuggerPanel = '<div id="debuggerPanel" class="debuggerAccordion">' . \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data')->getDataAsHtml() . '</div>';
        $content = (string)$response->getBody();

        $pos = strripos($content, '</body>');
        if (false === $pos) {
            return;
        }

        $content = substr($content, 0, $pos) . $debuggerPanel . substr($content, $pos);

        // Update the response content
        $response->setBody($content);
    }
}
