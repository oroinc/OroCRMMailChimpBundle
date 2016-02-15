<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

abstract class AbstractMemberActivityIterator extends AbstractSubordinateIterator
{
    const CAMPAIGN_KEY = 'campaign';

    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @param \Iterator $campaignsIterator
     * @param MailChimpClient $client
     */
    public function __construct(\Iterator $campaignsIterator, MailChimpClient $client)
    {
        parent::__construct($campaignsIterator);
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($campaign)
    {
        return new \CallbackFilterIterator(
            $this->createResultIterator($campaign),
            function (&$current) use ($campaign) {
                if ($current === null) {
                    return false;
                }

                $current[self::CAMPAIGN_KEY] = $campaign;
                return true;
            }
        );
    }

    /**
     * Create Campaign Aware Iterator
     *
     * @param Campaign $campaign
     * @return \Iterator
     */
    abstract protected function createResultIterator(Campaign $campaign);
}
