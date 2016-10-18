<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\MailChimpBundle\Entity\MemberActivity;

class MemberSendActivityDataConverter extends AbstractMemberActivityDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if ($importedRecord['status'] === MemberActivity::ACTIVITY_SENT) {
            $importedRecord['action'] = MemberActivity::ACTIVITY_SENT;
        } else {
            $importedRecord['action'] = MemberActivity::ACTIVITY_BOUNCE;
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }
}
