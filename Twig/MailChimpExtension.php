<?php

namespace OroCRM\Bundle\MailChimpBundle\Twig;

use Doctrine\Common\Persistence\ManagerRegistry;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class MailChimpExtension extends \Twig_Extension
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
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('orocrm_mailchimp_campaign_stats', [$this, 'getCampaignStats']),
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_mailchimp';
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @return array|\OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment[]
     */
    public function getCampaignStats(EmailCampaign $emailCampaign)
    {
        $campaign = $this->registry->getManager()
            ->getRepository('OroCRMMailChimpBundle:Campaign')
            ->findOneBy(['emailCampaign' => $emailCampaign]);
        return $campaign;
    }
}
