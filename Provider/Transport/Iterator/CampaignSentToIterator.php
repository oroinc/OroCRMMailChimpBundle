<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Exception;

class CampaignSentToIterator extends AbstractCampaignAwareIterator
{
    /**
     * @return array
     * @throws Exception
     */
    protected function getResult()
    {
        $sentTo = $this->client->getCampaignSentToReport($this->getArguments());

        return [
            'data' => $sentTo['sent_to'],
            'total' => $sentTo['total_items']
        ];
    }
}
