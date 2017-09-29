<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 */

namespace SalesIgniter\Debugger\Block\CustomFrontend;

class Logviewer extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'SalesIgniter_Debugger::custom/logviewer.phtml';
    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \SalesIgniter\Debugger\Helper\Data
     */
    private $helperDebugger;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \SalesIgniter\Debugger\Helper\Data               $helperDebugger
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \SalesIgniter\Debugger\Helper\Data $helperDebugger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->helperDebugger = $helperDebugger;
    }

    /**
     * This function is good to update the fields on runtime
     * Might be needed at some point but is very important to know that only
     * component and config are used. For now because we are using the template
     * processor is better to update on there if there are some dynamic fields.
     * Can be used to update some dynamic text or do element specific stuff.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getJsLayout()
    {
        $jsLayout = $this->jsLayout;

        return $this->helperDebugger->serialize($jsLayout, true);
    }
}
