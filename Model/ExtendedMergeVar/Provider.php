<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class Provider implements CompositeProviderInterface
{
    /**
     * @var array|ProviderInterface[]
     */
    protected $providers = [];

    /**
     * {@inheritdoc}
     */
    public function addProvider(ProviderInterface $provider)
    {
        if (in_array($provider, $this->providers, true)) {
            return;
        }
        $this->providers[] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function provideExtendedMergeVars(MarketingList $marketingList)
    {
        $vars = [];
        foreach ($this->providers as $provider) {
            $currentProviderVars = $provider->provideExtendedMergeVars($marketingList);
            if (!empty($currentProviderVars)) {
                $vars = array_merge($vars, $currentProviderVars);
            }
        }

        return $vars;
    }
}
