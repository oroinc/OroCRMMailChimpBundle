<?php

namespace Oro\Bundle\MailChimpBundle\Model\MarketingList;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class DataGridProvider extends MarketingListProvider implements DataGridProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDataGridConfiguration(MarketingList $marketingList)
    {
        if ($marketingList->isManual()) {
            $mixin = MarketingListProvider::MANUAL_RESULT_ENTITIES_MIXIN;
        } else {
            $mixin = MarketingListProvider::RESULT_ENTITIES_MIXIN;
        }

        $dataGrid = $this->getMarketingListDataGrid($marketingList, $mixin);
        return $dataGrid->getConfig();
    }
}
