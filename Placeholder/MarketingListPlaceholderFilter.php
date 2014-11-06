<?php

namespace OroCRM\Bundle\MailChimpBundle\Placeholder;

use Doctrine\Common\Persistence\ManagerRegistry;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class MarketingListPlaceholderFilter
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
     * @param MarketingList $marketingList
     * @return bool
     */
    public function isApplicableOnMarketingList($marketingList)
    {
        if ($marketingList instanceof MarketingList) {
            return (bool)$this->registry->getManager()
                ->getRepository('OroCRMMailChimpBundle:StaticSegment')
                ->findOneBy(['marketingList' => $marketingList]);
        }

        return false;
    }
}
