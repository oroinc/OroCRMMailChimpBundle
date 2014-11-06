<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarInterface;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentAwareInterface;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MemberSyncDataConverter extends MemberDataConverter implements StaticSegmentAwareInterface
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * {@inheritdoc}
     */
    public function getStaticSegment()
    {
        return $this->context->getOption(StaticSegmentAwareInterface::OPTION_SEGMENT);
    }

    /**
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     */
    public function setContactInformationFieldsProvider(
        ContactInformationFieldsProvider $contactInformationFieldsProvider
    ) {
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
    }

    /**
     * @param string $memberClassName
     */
    public function setMemberClassName($memberClassName)
    {
        $this->memberClassName = $memberClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $object = reset($importedRecord);
        $contactInformationFieldsValues = $this->getContactInformationFieldsValues($object);

        $item = [
            MergeVarInterface::FIELD_TYPE_EMAIL => reset($contactInformationFieldsValues),
            MergeVarInterface::TAG_EMAIL => reset($contactInformationFieldsValues),
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
     * @param object $object
     * @return array
     */
    protected function getContactInformationFieldsValues($object)
    {
        $contactInformationFields = $this->contactInformationFieldsProvider->getEntityTypedFields(
            $this->memberClassName,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        return $this->contactInformationFieldsProvider->getTypedFieldsValues(
            $contactInformationFields,
            $object
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
