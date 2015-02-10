<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

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
        $entityClassName   = !empty($importedRecord['entityClass']) ? $importedRecord['entityClass'] : null;

        if ($entityClassName) {
            $contactFieldsValues = $this->getContactInformationFieldsValues($entityClassName, $marketingListData);
        } else {
            $contactFieldsValues = [$marketingListData['email']];
        }

        $item = [
            MergeVarInterface::FIELD_TYPE_EMAIL => reset($contactFieldsValues),
            MergeVarInterface::TAG_EMAIL        => reset($contactFieldsValues),
            'status'                            => Member::STATUS_EXPORT,
        ];

        if (!empty($marketingListData['firstName'])) {
            $item[MergeVarInterface::TAG_FIRST_NAME] = $marketingListData['firstName'];
        }

        if (!empty($marketingListData['lastName'])) {
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
     * @param string $entityClassName
     * @param array  $data
     * @return array
     */
    protected function getContactInformationFieldsValues($entityClassName, $data)
    {
        $contactInformationFields = $this->contactInformationFieldsProvider->getEntityTypedFields(
            $entityClassName,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $values = $this->contactInformationFieldsProvider->getTypedFieldsValues(
            $contactInformationFields,
            $data
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
