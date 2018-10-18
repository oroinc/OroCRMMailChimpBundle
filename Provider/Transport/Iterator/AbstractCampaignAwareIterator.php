<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\MailChimpBundle\Entity\Campaign;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

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
            'campaign_id' => $this->campaign->getOriginId(),
            'offset' => (int)$this->offset / $this->batchSize,
            'count' => $this->batchSize,
        ];
    }
}
