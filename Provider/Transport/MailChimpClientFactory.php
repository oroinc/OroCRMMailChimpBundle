<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport;

class MailChimpClientFactory
{
    /**
     * @var string
     */
    protected $clientClass = 'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClient';

    /**
     * @var string
     */
    protected $apiVersion = MailChimpClient::LATEST_API_VERSION;

    /**
     * @param string $clientClass
     */
    public function setClientClass($clientClass)
    {
        $this->clientClass = $clientClass;
    }

    /**
     * @param string $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * Create MailChimp Client.
     *
     * @param string $apiKey
     * @return MailChimpClient
     */
    public function create($apiKey)
    {
        $client = new $this->clientClass($apiKey, $this->apiVersion);

        return $client;
    }
}
