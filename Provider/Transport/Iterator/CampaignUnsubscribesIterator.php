<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

class CampaignUnsubscribesIterator extends AbstractCampaignAwareIterator
{
    /**
     * @return array
     */
    protected function getResult()
    {
        return $this->client->getCampaignUnsubscribesReport($this->getArguments());
    }
}
