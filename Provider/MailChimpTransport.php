<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

use Guzzle\Plugin\Async\AsyncPlugin;
use OroCRM\Bundle\MailChimpBundle\Model\MailChimpClientFactory;
use ZfrMailChimp\Client\MailChimpClient;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use OroCRM\Bundle\MailChimpBundle\Exception\RequiredOptionException;

class MailChimpTransport implements TransportInterface
{
    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @var MailChimpClientFactory
     */
    protected $mailChimpClientFactory;

    /**
     * @param MailChimpClientFactory $mailChimpClientFactory
     */
    public function __construct(MailChimpClientFactory $mailChimpClientFactory)
    {
        $this->mailChimpClientFactory = $mailChimpClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function init(Transport $transportEntity)
    {
        $apiKey = $transportEntity->getSettingsBag()->get('apiKey');
        if (!$apiKey) {
            throw new RequiredOptionException('apiKey');
        }
        $this->client = $this->mailChimpClientFactory->create($apiKey);
    }

    /**
     * @return array
     */
    public function ping()
    {
        return $this->client->ping();
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
