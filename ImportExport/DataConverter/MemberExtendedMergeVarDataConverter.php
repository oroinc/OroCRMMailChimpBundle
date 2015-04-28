<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class MemberExtendedMergeVarDataConverter extends AbstractTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'static_segment_id' => 'staticSegment:id',
            'member_id' => 'member:id',
            'merge_var_values' => 'mergeVarValues'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \BadMethodCallException('Normalization is not implemented!');
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $itemData = parent::convertToImportFormat($importedRecord, $skipNullValues);
        if (empty($itemData['mergeVarValues'])) {
            $itemData['mergeVarValues'] = [];
        }
        return $itemData;
    }
}
