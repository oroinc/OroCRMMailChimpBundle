<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarProvider;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentAwareInterface;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MemberSyncDataConverter extends MemberDataConverter implements StaticSegmentAwareInterface
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var MergeVarProvider
     */
    protected $mergeVarsProvider;

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
     * @param MergeVarProvider $mergeVarsProvider
     */
    public function setMergeVarsProvider($mergeVarsProvider)
    {
        $this->mergeVarsProvider = $mergeVarsProvider;
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

        $contactInformationFieldsValues = $this->getContactInformationFieldsValues($importedRecord);

        $staticSegment = $this->getStaticSegment();
        $subscribersList = $staticSegment->getSubscribersList();

        $emailMergeVarName = $this->getEmailMergeVarName($subscribersList);

        $importedRecord = [
            $emailMergeVarName => reset($contactInformationFieldsValues),
            'status' => Member::STATUS_UNSUBSCRIBED,
            'subscribersList_id' => $staticSegment->getSubscribersList()->getId(),
            'channel_id' => $staticSegment->getChannel()->getId()
        ];

        $importedRecord = parent::convertToImportFormat($importedRecord);

        return $importedRecord;
    }

    /**
     * @param array $importedRecord
     * @return array
     */
    protected function getContactInformationFieldsValues(array $importedRecord)
    {
        $contactInformationFields = $this->contactInformationFieldsProvider->getEntityTypedFields(
            $this->memberClassName,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        return $this->contactInformationFieldsProvider->getTypedFieldsValues(
            $contactInformationFields,
            $importedRecord
        );
    }

    /**
     * @param SubscribersList $subscribersList
     * @return string
     */
    protected function getEmailMergeVarName(SubscribersList $subscribersList)
    {
        $mergeVarsFields = $this->mergeVarsProvider->getMergeVarFields($subscribersList);

        return $mergeVarsFields->getEmail()->getName();
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
