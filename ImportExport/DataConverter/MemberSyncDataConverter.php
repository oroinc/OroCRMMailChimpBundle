<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
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
     * @param FullNameInterface $object
     *
     * @return array
     */
    public function convertObjectToImportFormat(FullNameInterface $object)
    {
        $contactInformationFieldsValues = $this->getContactInformationFieldsValues($object);

        $staticSegment = $this->getStaticSegment();

        $item = [
            MergeVarInterface::FIELD_TYPE_EMAIL => reset($contactInformationFieldsValues),
            MergeVarInterface::TAG_EMAIL => reset($contactInformationFieldsValues),
            MergeVarInterface::TAG_FIRST_NAME => $object->getFirstName(),
            MergeVarInterface::TAG_LAST_NAME => $object->getLastName(),
            'status' => Member::STATUS_UNSUBSCRIBED,
            'subscribersList_id' => $staticSegment->getSubscribersList()->getId(),
            'channel_id' => $staticSegment->getChannel()->getId()
        ];

        return $item;
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
