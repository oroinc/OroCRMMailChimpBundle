<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\StaticSegmentIterator;

class StaticSegmentDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'id' => 'originId',
            'name' => 'name',
            'last_update' => 'updatedAt',
            'created_date' => 'createdAt',
            'last_reset' => 'lastReset',
            'member_count' => 'memberCount',
            'sync_status' => 'syncStatus',
            StaticSegmentIterator::SUBSCRIBERS_LIST_ID => 'subscribersList:originId',
            'subscribers_list_channel_id' => 'subscribersList:channel:id',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord['subscribers_list_channel_id'] = $this->context->getOption('channel');

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
