<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

abstract class AbstractCampaignAwareIterator extends AbstractMailChimpIterator
{
    /**
     * @var Campaign
     */
    protected $campaign;

    /**
     * @param MailChimpClient $client
     * @param Campaign $campaign
     * @param int $batchSize
     * @internal param array $filters
     */
    public function __construct(MailChimpClient $client, Campaign $campaign, $batchSize = self::BATCH_SIZE)
    {
        parent::__construct($client, $batchSize);
        $this->campaign = $campaign;
    }

    /**
     * @return array
     */
    protected function getArguments()
    {
        return [
            'cid' => $this->campaign->getOriginId(),
            'opts' => [
                'start' => (int)$this->offset / $this->batchSize,
                'limit' => $this->batchSize,
            ]
        ];
    }
}
