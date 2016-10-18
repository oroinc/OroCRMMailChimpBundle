<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\DataConverter;

class TemplateDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'origin_id' => 'originId',
            'preview_image' => 'previewImage',
            'date_created' => 'createdAt'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
