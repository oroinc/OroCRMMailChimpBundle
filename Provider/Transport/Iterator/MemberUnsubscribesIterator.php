<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;

class MemberUnsubscribesIterator extends AbstractMemberActivityIterator
{
    /**
     * Creates iterator of unsibscribed members for Campaign
     *
     * @param Campaign $campaign
     * @return \Iterator
     */
    protected function createResultIterator(Campaign $campaign)
    {
        return new CampaignUnsubscribesIterator($this->client, $campaign);
    }
}
