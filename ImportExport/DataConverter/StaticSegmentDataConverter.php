<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

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
            'member_count' => 'lastReset',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
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
