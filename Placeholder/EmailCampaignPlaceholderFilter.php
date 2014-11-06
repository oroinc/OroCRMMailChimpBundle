<?php

namespace OroCRM\Bundle\MailChimpBundle\Placeholder;

use Doctrine\Common\Persistence\ManagerRegistry;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class EmailCampaignPlaceholderFilter
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Checks the object is an instance of a given class.
     *
     * @param EmailCampaign $entity
     * @return bool
     */
    public function isApplicableOnEmailCampaign($entity)
    {
        if ($entity instanceof EmailCampaign && $entity->getTransport() == 'mailchimp') {
            $campaign = $this->registry->getManager()
                ->getRepository('OroCRMMailChimpBundle:Campaign')
                ->findOneBy(['emailCampaign' => $entity]);
            return (bool) $campaign;
        } else {
            return false;
        }
    }
}
