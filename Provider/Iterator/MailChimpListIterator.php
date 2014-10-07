<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Iterator;

class MailChimpListIterator extends AbstractMailChimpIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getResult()
    {
        return $this->client->getLists(
            ['start' => (int)$this->offset / self::BATCH_SIZE, 'limit' => self::BATCH_SIZE]
        );
    }
}
