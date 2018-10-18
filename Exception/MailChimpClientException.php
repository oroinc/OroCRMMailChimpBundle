<?php

namespace Oro\Bundle\MailChimpBundle\Exception;

use Exception;

class MailChimpClientException extends Exception implements MailChimpException
{
    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param int $code
     * @param string $detail
     * @return MailChimpClientException
     */
    public static function becauseStatusIsIncorect($code, $detail = '')
    {
        return new self(sprintf('Mailchimp returned status "%d" message: %s', $code, $detail));
    }

    /**
     * @param string $message
     * @return MailChimpClientException
     */
    public static function becauseListIdWasNotFound($message = 'List id not found in options')
    {
        return new self($message);
    }

    /**
     * @param string $message
     * @return MailChimpClientException
     */
    public static function becauseCampaignIdWasNotFound($message = 'Campaign id not found in options')
    {
        return new self($message);
    }

    /**
     * @param string $message
     * @return MailChimpClientException
     */
    public static function becauseStaticSegmentIdWasNotFound($message = 'Static segment id not found in options')
    {
        return new self($message);
    }

    /**
     * @param string $message
     * @return MailChimpClientException
     */
    public static function becauseResultIsNotAnArray(
        $message = 'Result should be an array, possible connection is not made'
    ) {
        return new self($message);
    }
}
