<?php

namespace Oro\Bundle\MailChimpBundle\Model\ExtendedMergeVar;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

interface ProviderInterface
{
    /**
     * Check that current provider is applicable for given marketing list.
     *
     * @param MarketingList $marketingList
     * @return bool
     */
    public function isApplicable(MarketingList $marketingList);

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
