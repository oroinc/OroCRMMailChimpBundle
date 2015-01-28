<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarInterface;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MemberSyncDataConverter extends MemberDataConverter
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     */
    public function __construct(ContactInformationFieldsProvider $contactInformationFieldsProvider)
    {
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $marketingListData = reset($importedRecord);
        $item = [
            MergeVarInterface::FIELD_TYPE_EMAIL => $marketingListData['email'],
            MergeVarInterface::TAG_EMAIL => $marketingListData['email'],
            'status' => Member::STATUS_EXPORT,
        ];

        if (isset($marketingListData['firstName'])) {
            $item[MergeVarInterface::TAG_FIRST_NAME] = $marketingListData['firstName'];
        }

        if (isset($marketingListData['lastName'])) {
            $item[MergeVarInterface::TAG_LAST_NAME] = $marketingListData['lastName'];
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
     * @param object $object
     * @return array
     */
    protected function getContactInformationFieldsValues($object)
    {
        $contactInformationFields = $this->contactInformationFieldsProvider->getEntityTypedFields(
            $object,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $values = $this->contactInformationFieldsProvider->getTypedFieldsValues(
            $contactInformationFields,
            $object
        );

        return array_filter($values);
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
