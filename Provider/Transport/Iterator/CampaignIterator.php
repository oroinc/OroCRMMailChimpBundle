<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

class CampaignIterator extends AbstractMailChimpIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getResult()
    {
        return $this->client->getCampaigns(
            ['start' => (int)$this->offset / self::BATCH_SIZE, 'limit' => self::BATCH_SIZE]
        );
    }
}
