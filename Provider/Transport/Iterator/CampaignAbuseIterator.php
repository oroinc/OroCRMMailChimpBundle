<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\MailChimpBundle\Entity\Campaign;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class CampaignAbuseIterator extends AbstractCampaignAwareIterator
{
    /**
     * @var string
     */
    protected $since;

    /**
     * @param MailChimpClient $client
     * @param Campaign $campaign
     * @param string $since
     * @param int $batchSize
     */
    public function __construct(MailChimpClient $client, Campaign $campaign, $since, $batchSize = self::BATCH_SIZE)
    {
        $this->since = $since;
        parent::__construct($client, $campaign, $batchSize);
    }

    /**
     * @return array
     */
    protected function getResult()
    {
        $arguments = $this->getArguments();
        if ($this->since) {
            $arguments['opts']['since'] = $this->since;
        }

        return $this->client->getCampaignAbuseReport($arguments);
    }
}
