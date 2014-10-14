<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\MergeVar;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

interface MergeVarProviderInterface
{
    /**
     * Get MergeVarFieldsInterface from SubscribersList according to it's config.
     *
     * @param SubscribersList $subscribersList
     * @return MergeVarFieldsInterface
     */
    public function getMergeVarFields(SubscribersList $subscribersList);

    /**
     * Assign values from MergeVarFieldsInterface to Member
     *
     * @param Member $member
     * @param MergeVarFieldsInterface $fields
     * @return void
     */
    public function assignMergeVarValues(Member $member, MergeVarFieldsInterface $fields);
}
