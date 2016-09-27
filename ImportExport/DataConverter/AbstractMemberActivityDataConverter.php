<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\DataConverter;

abstract class AbstractMemberActivityDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'campaign' => 'campaign'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $memberData = $importedRecord['member'];
        unset($importedRecord['member']);

        $importedRecord['member:originId'] = $memberData['web_id'];
        $importedRecord['email'] = $memberData['email'];

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
