<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Connector;

use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class StaticSegmentConnector extends AbstractMailChimpConnector implements TwoWaySyncConnectorInterface
{
    const TYPE = 'static_segment';
    const JOB_IMPORT = 'mailchimp_static_segment_import';
    const JOB_EXPORT = 'mailchimp_static_segment_export';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.mailchimp.connector.staticSegment.label';
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
        return $this->transport->getSegmentsToSync($this->getChannel());
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return self::JOB_EXPORT;
    }
}
