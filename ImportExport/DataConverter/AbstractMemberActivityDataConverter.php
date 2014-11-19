<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

abstract class AbstractMemberActivityDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord['member:originId'] = $importedRecord['member']['id'];
        $importedRecord['email'] = $importedRecord['member']['email'];

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
