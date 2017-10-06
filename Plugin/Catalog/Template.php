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
        if ($this->isPaymentResponse()) {
            return $html;
        }
        $domHtml = html5qp('<div>'.$html.'</div>');
        $isChanged = false;
        if (class_exists('\SalesIgniter\Debugger\Helper\Data') && \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Debugger\Helper\Data')->isEnabled()) {
            $originalHtml = $html;
            $originalHtml5 = $domHtml->html();
            $originalHtml5 = substr($originalHtml5, 0, strlen($originalHtml5) - 8);
            if ($subject->getNameInLayout() === 'salesigniter.debugger.showlogsnav') {
                $this->_addLogviewer($domHtml, $isChanged);
            }
        }
        if ($isChanged) {
            $htmlString = $domHtml->html();
            $htmlString = substr($htmlString, 0, strlen($htmlString) - 8);
            $htmlString = str_replace($originalHtml5, '', $htmlString);

            return $originalHtml.$this->removeHtmlTags($htmlString);
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
