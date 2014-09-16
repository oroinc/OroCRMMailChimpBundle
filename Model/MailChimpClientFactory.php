<?php

namespace OroCRM\Bundle\MailChimpBundle\Model;

use ZfrMailChimp\Client\MailChimpClient;

class MailChimpClientFactory
{
    /**
     * Create MailChimp Client.
     *
     * @param string $apiKey
     * @return MailChimpClient $client
     */
    public function create($apiKey)
    {
        $client = new MailChimpClient($apiKey);

        return $client;
    }
}
