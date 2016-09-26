<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Connector;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

class MemberAbuseActivityConnector extends AbstractMailChimpConnector implements ConnectorInterface
{
    const TYPE = 'member_activity_abuse';
    const JOB_IMPORT = 'mailchimp_member_activity_import_abuse';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.mailchimp.connector.member_activity_abuse.label';
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
        return $this->transport->getCampaignAbuseReport($this->getChannel(), $this->getLastSyncDate());
    }
}
