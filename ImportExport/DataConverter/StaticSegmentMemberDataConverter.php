<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

/**
 * @todo REMOVE ME!
 */
class StaticSegmentMemberDataConverter extends AbstractTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'member_id' => 'member:id',
            'static_segment_id' => 'staticSegment:id',
            'state' => 'state',
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
