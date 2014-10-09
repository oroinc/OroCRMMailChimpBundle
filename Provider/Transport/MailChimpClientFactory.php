<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport;

class MailChimpClientFactory
{
    /**
     * @var string
     */
    protected $clientClass;

    /**
     * @var string
     */
    protected $apiVersion;

    /**
     * @param string $clientClass
     * @param string $apiVersion
     */
    public function __construct(
        $clientClass = 'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClient',
        $apiVersion = MailChimpClient::LATEST_API_VERSION
    ) {
        $this->clientClass = $clientClass;
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
