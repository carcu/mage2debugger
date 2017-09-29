<?php

namespace SalesIgniter\Debugger\Plugin\Catalog;

use Magento\Framework\View\Page\Config\Reader\Html;

/**
 * Class Template.
 *
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.OverallComplexity)
 */
class Template
{
    /**
     * @var SalesIgniter\Debugger\Helper\Data
     */
    protected $helperDebugger;
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \SalesIgniter\Debugger\Helper\Data      $helperDebugger
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry             $coreRegistry
     */
    public function __construct(
        \SalesIgniter\Debugger\Helper\Data $helperDebugger,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->helperDebugger = $helperDebugger;
        $this->layout = $layout;
        $this->coreRegistry = $coreRegistry;
        $this->request = $request;
    }

    public function isPaymentResponse()
    {
        return strpos($this->request->getFullActionName(), '_response') !== false;
    }

    private function removeHtmlTags($html)
    {
        //$html = preg_replace("/<html[^>]+\>/i", '', $html);
        $html = str_replace('<html>', '', $html);
        $html = str_replace('</html>', '', $html);
        // $html = str_replace('<!DOCTYPE html>', '', $html);
        // $html = str_replace('<br></br>', '<br />', $html);
        return $html;
    }

    /**
     * Function which add the pricing blocks and styles.
     *
     * @param \QueryPath\DOMQuery $dom
     *
     * @return string
     */
    private function _addSirentDebugger(&$dom)
    {
        /** @var \SalesIgniter\Debugger\Block\Footer\SirentDebugger $block */
        $block = $this->layout->createBlock('\SalesIgniter\Debugger\Block\Footer\SirentDebugger');
        $html = $block->toHtml();
        $html = html5qp($html);
        $dom->append($html);
    }

    /**
     * Function which add the pricing blocks and styles.
     *
     * @param \QueryPath\DOMQuery $dom
     *
     * @return string
     */
    private function _addSirentDebuggerFooter(&$dom)
    {
        /** @var \SalesIgniter\Debugger\Block\Footer\SirentDebugger $block */
        $block = $this->layout->createBlock('\SalesIgniter\Debugger\Block\Footer\SirentDebuggerFooter');
        $html = $block->toHtml();
        $html = html5qp($html);
        $dom->append($html);
    }

    /**
     * Function to add pricing and stylesheets.
     *
     * @param $subject
     * @param $domHtml
     * @param $isChanged
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _addDebuggerAssets($subject, &$domHtml, &$isChanged)
    {
        if ($subject->getNameInLayout() === 'head.components' /*|| $subject->getNameInLayout() === 'copyright'*/) {
            $this->_addSirentDebugger($domHtml);
            //$this->_appendFrontendGeneralStyles($domHtml);
            $isChanged = true;
        }
        if ($subject->getNameInLayout() === 'copyright' /*|| $subject->getNameInLayout() === 'copyright'*/) {
            $this->_addSirentDebuggerFooter($domHtml);
            //$this->_appendFrontendGeneralStyles($domHtml);
            $isChanged = true;
        }
    }

    /**
     * Retrieve block view from file (template).
     *
     * @param \Magento\Framework\View\Element\Template $subject
     * @param \Closure                                 $proceed
     * @param string                                   $fileName
     *
     * @return string
     *
     * @throws \RuntimeException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundFetchView(
        \Magento\Framework\View\Element\Template $subject,
        \Closure $proceed,
        $fileName
    ) {
        $html = $proceed($fileName);
        if (class_exists('\SalesIgniter\Debugger\Helper\Data')) {
            $myDebugger = \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data');
            $myDebugger->addData($subject->getNameInLayout(), 'template');
        }

        if ($this->isPaymentResponse()) {
            return $html;
        }
        $domHtml = html5qp('<div>'.$html.'</div>');
        $isChanged = false;
        if (class_exists('\SalesIgniter\Debugger\Helper\Data') && \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data')->isEnabled()) {
            $originalHtml = $html;
            $originalHtml5 = $domHtml->html();
            $originalHtml5 = substr($originalHtml5, 0, strlen($originalHtml5) - 8);
            //I have modified the way it shows the rrors. It writes everything to folder
            //and it reads from there
            //$this->_addDebuggerAssets($subject, $domHtml, $isChanged);
            if ($subject->getNameInLayout() === 'salesigniter.debugger.showlogsnav') {
                $this->_addLogviewer($domHtml, $isChanged);
            }
        }
        if ($isChanged) {
            $htmlString = $domHtml->html();
            $htmlString = substr($htmlString, 0, strlen($htmlString) - 8);
            $htmlString = str_replace($originalHtml5, '', $htmlString);

            return $originalHtml.$this->removeHtmlTags($htmlString);
            // return substr($htmlString, 5, strlen($htmlString) - 11);
        } else {
            return $html;
        }
    }

    /**
     * @param $dom
     * @param $isChanged
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _addLogviewer(&$dom, &$isChanged)
    {
        /** @var \SalesIgniter\Debugger\Block\CustomFrontend\Logviewer $block */
        $block = $this->layout->createBlock(
            '\SalesIgniter\Debugger\Block\CustomFrontend\Logviewer', '', ['data' => [
                'jsLayout' => [
                    'components' => [
                        'block-logviewer' => [
                            'component' => 'SalesIgniter_Debugger/js/custom/sifancytree',
                            'config' => [
                                'url' => '',
                                'classElementTrigger' => '#sitree',
                            ],
                        ],
                    ],
                ],
            ],
            ]
        );
        $isChanged = true;
        $dom->append($block->toHtml());
    }
}
