<?php

namespace Oro\Bundle\MailChimpBundle\Model\MergeVar;

use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;

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
