<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class CampaignIterator extends AbstractMailChimpIterator
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * @param MailChimpClient $client
     * @param array $filters
     * @param int $batchSize
     */
    public function __construct(MailChimpClient $client, array $filters = [], $batchSize = self::BATCH_SIZE)
    {
        parent::__construct($client, $batchSize);
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResult()
    {
        $arguments = ['start' => (int)$this->offset / $this->batchSize, 'limit' => $this->batchSize];
        if ($this->filters) {
            $arguments['filters'] = $this->filters;
        }
        return $this->client->getCampaigns($arguments);
    }
}
