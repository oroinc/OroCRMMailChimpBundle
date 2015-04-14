<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListSourceInterface;

class DecisionHandler
{
    /**
     * @var array
     */
    private $allowedSources;

    public function __construct()
    {
        $this->allowedSources = [];
    }

    /**
     * Allows using MarketingList Segment definition as the ExtendedMergeVars if MarketingList source equal to given
     * @param MarketingListSourceInterface $source
     * @return void
     */
    public function allowExtendedMergeVar(MarketingListSourceInterface $source)
    {
        if (in_array($source->getCode(), $this->allowedSources, true)) {
            return;
        }
        $this->allowedSources[] = $source->getCode();
    }

    /**
     * Retrieves allowed MarketingList sources for the ExtendedMergeVars
     * @return array
     */
    public function getAllowedSources()
    {
        return $this->allowedSources;
    }

    /**
     * Checks if MarketingList allowed to be used for the ExtendedMergeVars
     * @param MarketingList $marketingList
     * @return bool
     */
    public function isAllow(MarketingList $marketingList)
    {
        return in_array($marketingList->getSource(), $this->allowedSources, true);
    }
}
