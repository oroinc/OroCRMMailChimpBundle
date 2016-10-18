<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

class CampaignSentToIterator extends AbstractCampaignAwareIterator
{
    /**
     * @return array
     */
    protected function getResult()
    {
        return $this->client->getCampaignSentToReport($this->getArguments());
    }
}
