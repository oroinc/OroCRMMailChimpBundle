<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\TrackingBundle\ImportExport\DataConverter;

class TemplateDataConverter extends DataConverter
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
