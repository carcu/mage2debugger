<?php

namespace SalesIgniter\Debugger\Framework;

class LocalizedException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception                $cause
     */
    public function __construct(Phrase $phrase, \Exception $cause = null)
    {
        $this->phrase = $phrase;
        parent::__construct($phrase->render(), $cause);
    }
}
