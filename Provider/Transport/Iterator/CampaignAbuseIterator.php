<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Exception;
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
     * @throws Exception
     */
    protected function getResult()
    {
        $arguments = $this->getArguments();

        $abuseReports = $this->client->getCampaignAbuseReport($arguments);

        return [
            'data' => $abuseReports['abuse_reports'],
            'total' => $abuseReports['total_items']
        ];
    }
}
