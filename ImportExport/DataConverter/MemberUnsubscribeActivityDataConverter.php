<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;

class MemberUnsubscribeActivityDataConverter extends AbstractMemberActivityDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord['action'] = MemberActivity::ACTIVITY_UNSUB;

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }
}
