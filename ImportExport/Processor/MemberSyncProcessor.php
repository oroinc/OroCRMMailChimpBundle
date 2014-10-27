<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Processor;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MemberSyncProcessor extends ImportProcessor
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     */
    public function setContactInformationFieldsProvider(
        ContactInformationFieldsProvider $contactInformationFieldsProvider
    ) {
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $contactInformationFields = $this->contactInformationFieldsProvider->getEntityTypedFields(
            $item,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $contactInformationFieldsValues = $this->contactInformationFieldsProvider->getTypedFieldsValues(
            $contactInformationFields,
            $item
        );

        $member = new Member();
        $member
            ->setEmail(reset($contactInformationFieldsValues))
            ->setStatus(Member::STATUS_UNSUBSCRIBED);

        return $member;
    }
}
