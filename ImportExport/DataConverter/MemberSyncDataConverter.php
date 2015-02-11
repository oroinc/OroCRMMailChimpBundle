<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarInterface;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MemberSyncDataConverter extends MemberDataConverter
{
    const FIRST_NAME_KEY      = 'firstName';
    const LAST_NAME_KEY       = 'lastName';
    const SUBSCRIBER_LIST_KEY = 'subscribersList_id';

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
        $entityClassName   = !empty($importedRecord['entityClass']) ? $importedRecord['entityClass'] : null;
        if ($entityClassName) {
            $contactFieldsValues = $this->getContactInformationFieldsValues($entityClassName, $importedRecord);
        } elseif (!empty($importedRecord['email'])) {
            $contactFieldsValues = [$importedRecord['email']];
        }

        $item = [
            MergeVarInterface::FIELD_TYPE_EMAIL => reset($contactFieldsValues),
            MergeVarInterface::TAG_EMAIL        => reset($contactFieldsValues),
            'status'                            => Member::STATUS_EXPORT,
        ];

        if (!empty($importedRecord[self::FIRST_NAME_KEY])) {
            $item[MergeVarInterface::TAG_FIRST_NAME] = $importedRecord[self::FIRST_NAME_KEY];
        }

        if (!empty($importedRecord[self::LAST_NAME_KEY])) {
            $item[MergeVarInterface::TAG_LAST_NAME] = $importedRecord[self::LAST_NAME_KEY];
        }

        if (!empty($importedRecord[self::SUBSCRIBER_LIST_KEY])) {
            $item['subscribersList_id'] = $importedRecord[self::SUBSCRIBER_LIST_KEY];
        }

        if ($this->context->getOption('channel')) {
            $item['channel_id'] = $this->context->getOption('channel');
        }

        return parent::convertToImportFormat($item, $skipNullValues);
    }

    /**
     * @param string $entityClassName
     * @param array  $data
     * @return array
     */
    protected function getContactInformationFieldsValues($entityClassName, array $data)
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
