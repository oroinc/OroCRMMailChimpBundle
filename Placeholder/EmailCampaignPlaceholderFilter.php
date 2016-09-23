<?php

namespace OroCRM\Bundle\MailChimpBundle\Placeholder;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\MailChimpBundle\Transport\MailChimpTransport;

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
        if ($entity instanceof EmailCampaign && $entity->getTransport() === MailChimpTransport::NAME) {
            $campaign = $this->registry->getManager()
                ->getRepository('OroCRMMailChimpBundle:Campaign')
                ->findOneBy(['emailCampaign' => $entity]);
            return (bool) $campaign;
        } else {
            return false;
        }
    }
}
