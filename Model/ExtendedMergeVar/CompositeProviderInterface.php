<?php

namespace Oro\Bundle\MailChimpBundle\Model\ExtendedMergeVar;

interface CompositeProviderInterface extends ProviderInterface
{
    /**
     * Adds external provider
     *
     * @param ProviderInterface $provider
     * @return void
     */
    public function addProvider(ProviderInterface $provider);
}
