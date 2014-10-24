<?php

namespace OroCRM\Bundle\MailChimpBundle\Acl\Voter;

use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class EmailCampaignVoter extends AbstractEntityVoter
{
    const ENTITY = 'OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign';

    /**
     * @var array
     */
    protected $supportedAttributes = ['EDIT'];

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === self::ENTITY;
    }

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
        $emailCampaign = $this->registry->getManager()
            ->getRepository('OroCRMCampaignBundle:EmailCampaign')
            ->find($entityId);
        return $emailCampaign ? $emailCampaign->isSent() : false;
    }
}
