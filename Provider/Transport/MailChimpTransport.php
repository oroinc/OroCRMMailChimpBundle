<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport;

use ZfrMailChimp\Client\MailChimpClient;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\Template;
use OroCRM\Bundle\MailChimpBundle\Exception\RequiredOptionException;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\CampaignIterator;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\ListIterator;

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
     * @link http://apidocs.mailchimp.com/api/2.0/helper/ping.php
     * @return array
     */
    public function ping()
    {
        return $this->client->ping();
    }

    /**
     * @link http://apidocs.mailchimp.com/api/2.0/campaigns/list.php
     * @return CampaignIterator
     */
    public function getCampaigns()
    {
        return new CampaignIterator($this->client);
    }

    /**
     * @link http://apidocs.mailchimp.com/api/2.0/lists/list.php
     * @return ListIterator
     */
    public function getLists()
    {
        return new ListIterator($this->client);
    }

    /**
     * Get list of MailChimp Templates.
     *
     * @link http://apidocs.mailchimp.com/api/2.0/templates/list.php
     * @return array
     */
    public function getTemplates()
    {
        return $this->client->getTemplates(
            [
                'types' => [
                    Template::TYPE_USER => true,
                    Template::TYPE_GALLERY => true,
                    Template::TYPE_BASE => true
                ],
                'filters' => [
                    'include_drag_and_drop' => true
                ]
            ]
        );
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
