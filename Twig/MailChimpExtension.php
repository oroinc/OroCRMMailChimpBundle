<?php

namespace OroCRM\Bundle\MailChimpBundle\Twig;

use Doctrine\Common\Persistence\ManagerRegistry;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

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
            new \Twig_SimpleFunction('orocrm_mailchimp_email_campaign', [$this, 'getEmailCampaign']),
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
     * @param MarketingList $marketingList
     * @return array|\OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment[]
     */
    public function getEmailCampaign(MarketingList $marketingList)
    {
        $staticSegment = $this->registry->getManager()
            ->getRepository('OroCRMMailChimpBundle:StaticSegment')
            ->findBy(['marketingList' => $marketingList]);
        return $staticSegment;
    }
}
