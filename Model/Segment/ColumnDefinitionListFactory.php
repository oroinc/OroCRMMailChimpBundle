<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Segment;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

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
        return $columnDefinitionList;
    }
}
