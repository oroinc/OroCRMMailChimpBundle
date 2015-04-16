<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\MarketingList;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

interface DataGridProviderInterface
{
    /**
     * @param MarketingList $marketingList
     * @return array
     */
    public function getDataGridColumns(MarketingList $marketingList);
}
