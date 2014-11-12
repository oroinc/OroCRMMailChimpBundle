<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarInterface;

class MemberSyncDataConverter extends MemberDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        /** @var Member $object */
        $object = reset($importedRecord);

        $item = [
            MergeVarInterface::FIELD_TYPE_EMAIL => $object->getEmail(),
            MergeVarInterface::TAG_EMAIL => $object->getEmail(),
            'status' => Member::STATUS_EXPORT,
        ];

        if ($object instanceof FirstNameInterface) {
            $item[MergeVarInterface::TAG_FIRST_NAME] = $object->getFirstName();
        }

        if ($object instanceof LastNameInterface) {
            $item[MergeVarInterface::TAG_LAST_NAME] = $object->getLastName();
        }

        if (!empty($importedRecord['subscribersList_id'])) {
            $item['subscribersList_id'] = $importedRecord['subscribersList_id'];
        }

        if (!empty($importedRecord['channel_id'])) {
            $item['channel_id'] = $importedRecord['channel_id'];
        }

        return parent::convertToImportFormat($item, $skipNullValues);
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
