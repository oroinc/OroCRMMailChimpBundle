<?php

namespace OroCRM\Bundle\MailChimpBundle\Exception;

class RequiredOptionException extends \Exception implements MailChimpException
{
    /**
     * @param string $optionName
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($optionName, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Option "%s" is required', $optionName);
        parent::__construct($message, $code, $previous);
    }
}
