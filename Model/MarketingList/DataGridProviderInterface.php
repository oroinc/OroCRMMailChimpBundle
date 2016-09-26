<?php

namespace Oro\Bundle\MailChimpBundle\Model\MarketingList;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

interface DataGridProviderInterface
{
    /**
     * @param MarketingList $marketingList
     * @return DatagridConfiguration
     */
    public function getDataGridConfiguration(MarketingList $marketingList);
}
