<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;

class CampaignConnector extends AbstractMailChimpConnector implements TwoWaySyncConnectorInterface, ConnectorInterface
{
    const TYPE = 'campaign';
    const JOB_IMPORT = 'mailchimp_campaign_import';
    const JOB_EXPORT = 'mailchimp_campaign_export';

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
        return $this->transport->getCampaigns(Campaign::STATUS_SENT, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        throw new \BadMethodCallException();
    }
}
