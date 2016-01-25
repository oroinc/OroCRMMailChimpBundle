<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class MemberActivityIterator extends AbstractMemberActivityIterator
{
    const EMAIL_KEY = 'email';

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param \Iterator $campaigns
     * @param MailChimpClient $client
     * @param array $parameters
     * @param array $sinceMap
     */
    public function __construct(
        \Iterator $campaigns,
        MailChimpClient $client,
        array $parameters = [],
        array $sinceMap = []
    ) {
        parent::__construct($campaigns, $client);

        $this->parameters = $parameters;
        $this->sinceMap = $sinceMap;
    }

    /**
     * Creates iterator of member activities for campaign
     *
     * @param Campaign $campaign
     * @return \Iterator
     */
    protected function createResultIterator(Campaign $campaign)
    {
        $parameters = $this->parameters;
        $parameters['id'] = $campaign->getOriginId();
        if (!empty($this->sinceMap[$campaign->getOriginId()]['since'])) {
            $parameters['since'] = $this->sinceMap[$campaign->getOriginId()]['since'];
        }

        return $this->createExportIterator(MailChimpClient::EXPORT_CAMPAIGN_SUBSCRIBER_ACTIVITY, $parameters);
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return \Iterator
     */
    protected function createExportIterator($method, array $parameters)
    {
        return new FlattenIterator(
            new ExportIterator($this->client, $method, $parameters, false),
            self::EMAIL_KEY,
            (bool)$parameters['include_empty'],
            2
        );
    }
}
