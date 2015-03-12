<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Connector;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class ExtendedMergeVarConnector extends AbstractMailChimpConnector implements
    TwoWaySyncConnectorInterface,
    ConnectorInterface
{
    const TYPE = 'extended_merge_var';
    const JOB_IMPORT = 'mailchimp_extended_merge_var_import';
    const JOB_EXPORT = 'mailchimp_extended_merge_var_export';

    /**
     * Returns label for UI
     *
     * @return string
     */
    public function getLabel()
    {
        return 'orocrm.mailchimp.connector.extendedMergeVar.label';
    }

    /**
     * Returns type name, the same as registered in service tag
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Returns entity name that will be used for matching "import processor"
     *
     * @return string
     */
    public function getImportEntityFQCN()
    {
        return $this->entityName;
    }

    /**
     * Returns job name for import
     *
     * @return string
     */
    public function getImportJobName()
    {
        return self::JOB_IMPORT;
    }

    /**
     * @return string
     */
    public function getExportJobName()
    {
        return self::JOB_EXPORT;
    }

    /**
     * Return source iterator to read from
     *
     * @return \Iterator
     */
    protected function getConnectorSource()
    {
        return $this->transport->getSegmentsToSync($this->getChannel());
    }
}
