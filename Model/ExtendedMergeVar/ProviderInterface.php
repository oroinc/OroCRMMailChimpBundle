<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

interface ProviderInterface
{
    /**
     * Retrieves Extended Merge Vars array.
     * Example:
     *  [
     *      [
     *          'name' => 'var_name',
     *          'label' => 'Var Label'
     *      ]
     *  ]
     *
     * @param MarketingList $marketingList
     * @return array
     */
    public function provideExtendedMergeVars(MarketingList $marketingList);
}
