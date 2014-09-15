<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

use Guzzle\Plugin\Async\AsyncPlugin;
use OroCRM\Bundle\MailChimpBundle\Exception\RequiredOptionException;
use ZfrMailChimp\Client\MailChimpClient;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

class MailChimpTransport implements TransportInterface
{
    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    public function init(Transport $transportEntity)
    {
        $apiKey = $transportEntity->getSettingsBag()->get('apiKey');
        if (!$apiKey) {
            throw new RequiredOptionException('apiKey');
        }
        $this->client = $this->createClient($apiKey);
    }

    /**
     * Create ZFRMailChimp Client
     *
     * @param string $apiKey
     * @return MailChimpClient $client
     */
    public function createClient($apiKey)
    {
        $client = new MailChimpClient($apiKey);
        $client->addSubscriber(new AsyncPlugin());

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.mailchimp.integration_transport.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return 'orocrm_mailchimp_integration_transport_setting_type';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'OroCRM\\Bundle\\MailChimpBundle\\Entity\\MailChimpTransport';
    }
}
