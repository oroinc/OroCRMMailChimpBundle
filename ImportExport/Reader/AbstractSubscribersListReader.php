<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

abstract class AbstractSubscribersListReader extends AbstractIteratorBasedReader
{
    /**
     * @var string
     */
    protected $subscribersListClassName;

    /**
     * @param string $subscribersListClassName
     */
    public function setSubscribersListClassName($subscribersListClassName)
    {
        $this->subscribersListClassName = $subscribersListClassName;
    }
}
