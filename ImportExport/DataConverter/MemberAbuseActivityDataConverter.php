<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;

class MemberAbuseActivityDataConverter extends AbstractMemberActivityDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord['action'] = MemberActivity::ACTIVITY_ABUSE;

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }
}
