<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

class MemberActivityDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'timestamp' => 'activityTime',
            'campaign_id' => 'campaign:id',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $channel = $this->context->getOption('channel');
        $importedRecord['member:email'] = $importedRecord['email'];

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
