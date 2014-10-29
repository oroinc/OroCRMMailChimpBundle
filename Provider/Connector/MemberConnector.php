<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Connector;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class MemberConnector extends AbstractMailChimpConnector implements TwoWaySyncConnectorInterface, ConnectorInterface
{
    const TYPE = 'member';
    const JOB_IMPORT = 'mailchimp_member_import';
    const JOB_EXPORT = 'mailchimp_member_export';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.mailchimp.connector.member.label';
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
        return $this->transport->getMembersToSync($this->getLastSyncDate());
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return self::JOB_EXPORT;
    }
}
