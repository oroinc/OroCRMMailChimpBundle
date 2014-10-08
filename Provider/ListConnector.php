<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class ListConnector extends AbstractMailChimpConnector implements TwoWaySyncConnectorInterface, ConnectorInterface
{
    const TYPE = 'list';
    const JOB_IMPORT = 'mailchimp_list_import';
    const JOB_EXPORT = 'mailchimp_list_export';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.mailchimp.connector.list.label';
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
        return $this->transport->getLists();
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return;
    }
}