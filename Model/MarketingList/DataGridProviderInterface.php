<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\MarketingList;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

interface DataGridProviderInterface
{
    /**
     * @param MarketingList $marketingList
     * @return DatagridConfiguration
     */
    public function getDataGridConfiguration(MarketingList $marketingList);
}
