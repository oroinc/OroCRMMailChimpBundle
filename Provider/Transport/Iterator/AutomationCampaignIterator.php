<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;

class AutomationCampaignIterator extends CampaignIterator
{
    /**
     * @var CampaignIterator
     */
    protected $campaignIterator;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @param CampaignIterator $campaignIterator
     */
    public function __construct(CampaignIterator $campaignIterator)
    {
        parent::__construct($campaignIterator->client, $campaignIterator->filters, $campaignIterator->batchSize);
        $this->campaignIterator = $campaignIterator;
        $this->filters['uses_segment'] = true;
        $this->filters['type'] = Campaign::TYPE_AUTO;
    }
}
