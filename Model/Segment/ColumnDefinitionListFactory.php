<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Segment;

use OroCRM\Bundle\AbandonedCartBundle\Model\MarketingList\AbandonedCartSource;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class ColumnDefinitionListFactory
{
    /**
     * @param MarketingList $marketingList
     * @return ColumnDefinitionListInterface
     */
    public function create(MarketingList $marketingList)
    {
        $segment = $marketingList->getSegment();
        $columnDefinitionList = new ColumnDefinitionList($segment);
        if ($marketingList->getSource() == AbandonedCartSource::SOURCE_CODE) {
            $columnDefinitionList = new CartColumnDefinitionList($columnDefinitionList);
        }
        return $columnDefinitionList;
    }
}
