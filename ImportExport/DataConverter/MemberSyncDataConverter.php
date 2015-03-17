<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarInterface;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MemberSyncDataConverter extends MemberDataConverter
{
    const EMAIL_KEY           = 'email';
    const FIRST_NAME_KEY      = 'firstName';
    const LAST_NAME_KEY       = 'lastName';
    const SUBSCRIBER_LIST_KEY = 'subscribersList_id';

    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var MemberExtendedMergeVarDataConverter
     */
    protected $memberExtendedMergeVarDataConverter;

    /**
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     * @param MemberExtendedMergeVarDataConverter $memberExtendedMergeVarDataConverter
     */
    public function __construct(
        ContactInformationFieldsProvider $contactInformationFieldsProvider,
        MemberExtendedMergeVarDataConverter $memberExtendedMergeVarDataConverter
    ) {
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
        $this->memberExtendedMergeVarDataConverter = $memberExtendedMergeVarDataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (!empty($importedRecord['entityClass'])) {
            $entityClassName = $importedRecord['entityClass'];
            $contactFieldsValues = $this->getContactInformationFieldsValues($entityClassName, $importedRecord);
            $importedRecord[self::EMAIL_KEY] = reset($contactFieldsValues);
        }

        $itemDataMap = [
            MergeVarInterface::FIELD_TYPE_EMAIL => self::EMAIL_KEY,
            MergeVarInterface::TAG_EMAIL        => self::EMAIL_KEY,
            MergeVarInterface::TAG_FIRST_NAME   => self::FIRST_NAME_KEY,
            MergeVarInterface::TAG_LAST_NAME    => self::LAST_NAME_KEY,
            'subscribersList_id'                => self::SUBSCRIBER_LIST_KEY,
        ];

        $item = array_map(
            function($value) use ($importedRecord) {
                return !empty($importedRecord[$value]) ? $importedRecord[$value] : null;
            },
            $itemDataMap
        );

        $item['status'] = Member::STATUS_EXPORT;
        if ($this->context->getOption('channel')) {
            $item['channel_id'] = $this->context->getOption('channel');
        }

        if (isset($importedRecord['extended_merge_vars'])) {
            $extendedMergeVars = $this
                ->memberExtendedMergeVarDataConverter
                ->convertToImportFormat($importedRecord, $skipNullValues);
            $item = array_merge($item, $extendedMergeVars);
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
