<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class ExtendedMergeVarDataConverter extends AbstractTableDataConverter
{
    /**
     * @inheritdoc
     */
    protected function getHeaderConversionRules()
    {
        return array(
            'name' => 'name',
            'label' => 'label',
            'static_segment_id' => 'staticSegment:id'
        );
    }

    /**
     * @inheritdoc
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
