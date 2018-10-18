<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport;

class MailChimpClientFactory
{
    /**
     * @var string
     */
    protected $clientClass = MailChimpClient::class;

    /**
     * @param string $clientClass
     */
    public function setClientClass($clientClass)
    {
        $this->clientClass = $clientClass;
    }

    /**
     * Create MailChimp Client.
     *
     * @param string $apiKey
     *
     * @return MailChimpClient
     */
    public function create($apiKey)
    {
        return new $this->clientClass($apiKey);
    }
}
