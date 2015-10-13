<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

class MemberDataConverter extends AbstractMemberDataConverter
{
    const IMPORT_DATA = '_is_import_data_';

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);
        // Add import mark to trigger simplified serializer to use
        $importedRecord[self::IMPORT_DATA] = true;

        return $importedRecord;
    }
}
