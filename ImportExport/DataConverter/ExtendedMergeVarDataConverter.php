<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class ExtendedMergeVarDataConverter extends AbstractTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'name' => 'name',
            'label' => 'label',
            'static_segment_id' => 'staticSegment:id',
            'state' => 'state'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \BadMethodCallException('Normalization is not implemented!');
    }
}
