<?php

namespace OroCRM\Bundle\MailChimpBundle\Acl\Voter;

use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;

class EmailCampaignVoter extends AbstractEntityVoter
{
    /**
     * @var array
     */
    protected $supportedAttributes = ['EDIT'];

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if ($this->isEmailCampaignSent($identifier)) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }

    /**
     * @param int $entityId
     * @return bool
     */
    protected function isEmailCampaignSent($entityId)
    {
        $emailCampaign = $this->doctrineHelper
            ->getRepository($this->className)
            ->find($entityId);

        if ($emailCampaign) {
            return $emailCampaign->isSent();
        }


        return false;
    }
}
