<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

use ZfrMailChimp\Client\MailChimpClient;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use OroCRM\Bundle\MailChimpBundle\Exception\RequiredOptionException;
use OroCRM\Bundle\MailChimpBundle\Model\MailChimpClientFactory;

/**
 * @link http://apidocs.mailchimp.com/api/2.0/
 * @link https://bitbucket.org/mailchimp/mailchimp-api-php/
 */
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
     * @return MailChimpClient
     */
    public function getClient()
    {
        return $this->client;
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
