<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Exception;

class CampaignUnsubscribesIterator extends AbstractCampaignAwareIterator
{
    /**
     * @return array
     * @throws Exception
     */
    protected function getResult()
    {
        $unsubscribes = $this->client->getCampaignUnsubscribesReport($this->getArguments());

        return [
            'data' => $unsubscribes['unsubscribes'],
            'total' => $unsubscribes['total_items'],
        ];
    }
}
