<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;
use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;

class MmbrExtdMergeVarDataConverter extends AbstractTableDataConverter
{
    /**
     * @var DataConverterInterface
     */
    private $mmbrExtdMergeVarExportDataConverter;

    /**
     * @param DataConverterInterface $mmbrExtdMergeVarExportDataConverter
     */
    public function __construct(DataConverterInterface $mmbrExtdMergeVarExportDataConverter)
    {
        $this->mmbrExtdMergeVarExportDataConverter = $mmbrExtdMergeVarExportDataConverter;
    }

    /**
     * @inheritdoc
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $extdMergeVarValues = $this
            ->mmbrExtdMergeVarExportDataConverter
            ->convertToImportFormat($importedRecord);
        $importedRecord['merge_var_values'] = $extdMergeVarValues;
        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * @inheritdoc
     */
    protected function getHeaderConversionRules()
    {
        return array(
            'static_segment_id' => 'staticSegment:id',
            'member_id' => 'member:id',
            'merge_var_values' => 'mergeVarValues',
            'state' => 'state'
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
