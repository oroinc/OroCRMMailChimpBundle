<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class MemberAbuseIterator extends AbstractMemberActivityIterator
{
    /**
     * @var string
     */
    protected $since;

    /**
     * @param \Iterator $campaignsIterator
     * @param string $since
     * @param MailChimpClient $client
     */
    public function __construct(\Iterator $campaignsIterator, $since, MailChimpClient $client)
    {
        parent::__construct($campaignsIterator, $client);

        $this->since = $since;
    }

    /**
     * Creates iterator of sent and bounce activities for Campaign
     *
     * @param Campaign $campaign
     * @return \Iterator
     */
    protected function createResultIterator(Campaign $campaign)
    {
        return new CampaignSentToIterator($this->client, $campaign, $this->since);
    }
}
