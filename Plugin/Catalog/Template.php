<?php

namespace SalesIgniter\Debugger\Plugin\Catalog;

use Magento\Framework\View\Page\Config\Reader\Html;

/**
 * Class Template
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.OverallComplexity)
 *
 * @package SalesIgniter\Rental\Plugin\Catalog
 */
class Template
{

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
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry             $coreRegistry
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $coreRegistry
    ) {
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
     * Function which add the pricing blocks and styles
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
        //$html = $this->removeHtmlTags($html->html());
        $dom->append($html);
    }

    /**
     * Function which add the pricing blocks and styles
     *
     * @param \QueryPath\DOMQuery $dom
     *
     * @return string
     */
    private function _addSirentDebuggerFooter(&$dom)
    {
        /** @var \SalesIgniter\Debugger\Block\Footer\SirentDebugger $block */
        $block = $this->layout->createBlock('\SalesIgniter\Debugger\Block\Footer\SirentDebuggerFooter');
        //$html = $this->removeHtmlTags($block->toHtml());
        $html = $block->toHtml();
        $html = html5qp($html);
        $dom->append($html);
    }

    /**
     * @param  \QueryPath\DOMQuery $dom
     *
     * @return string
     */
    private function _appendAdminCreateOrderUpdate(&$dom)
    {
        $html = '<script>
            require(["sirentcreateorder"], function(){
                
            });
            </script>';
        $html = html5qp($html);
        $dom->append($html);
    }

    /**
     * @param  \QueryPath\DOMQuery $dom
     *
     * @return string
     */
    private function _appendFrontendGeneralStyles(&$dom)
    {
        $html = '<script>
            require(["css!css/general/styles"], function(){
                
            });
            </script>';
        $html = html5qp($html);
        $dom->append($html);
    }

    /**
     * Function to add pricing and stylesheets
     *
     * @param $subject
     * @param $domHtml
     *
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
     * Retrieve block view from file (template)
     *
     * @param \Magento\Framework\View\Element\Template $subject
     * @param \Closure                                 $proceed
     * @param string                                   $fileName
     *
     * @return string
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

        if ($this->isPaymentResponse()) {
            return $html;
        }
        $domHtml = html5qp('<div>' . $html . '</div>');
        $isChanged = false;
        if (class_exists('\SalesIgniter\Debugger\Helper\Data') && \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data')->isEnabled()) {
            $originalHtml = $html;
            $originalHtml5 = $domHtml->html();
            $originalHtml5 = substr($originalHtml5, 0, strlen($originalHtml5) - 8);

            $this->_addDebuggerAssets($subject, $domHtml, $isChanged);
        }
        if ($isChanged) {
            $htmlString = $domHtml->html();
            $htmlString = substr($htmlString, 0, strlen($htmlString) - 8);
            $htmlString = str_replace($originalHtml5, '', $htmlString);
            return $originalHtml . $this->removeHtmlTags($htmlString);
            // return substr($htmlString, 5, strlen($htmlString) - 11);
        } else {
            return $html;
        }
    }
}
