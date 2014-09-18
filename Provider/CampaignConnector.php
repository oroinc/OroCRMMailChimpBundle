<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class CampaignConnector extends AbstractConnector implements TwoWaySyncConnectorInterface, ConnectorInterface
{
    const TYPE = 'campaign';
    const JOB_IMPORT = 'mailchimp_campaign_import';

    /**
     * @var MailChimpTransport
     */
    protected $transport;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.mailchimp.connector.campaign.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return $this->entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::JOB_IMPORT;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getCampaignIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return;
    }
}
