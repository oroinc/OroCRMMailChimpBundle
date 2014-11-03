<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class MemberActivityIterator extends AbstractSubordinateIterator
{
    const CAMPAIGN_KEY = 'campaign_id';
    const EMAIL_KEY = 'email';

    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param \Iterator $campaigns
     * @param MailChimpClient $client
     * @param array $parameters
     */
    public function __construct(\Iterator $campaigns, MailChimpClient $client, array $parameters = [])
    {
        parent::__construct($campaigns);

        $this->client = $client;
        $this->parameters = $parameters;
    }

    /**
     * Creates iterator of member activities for campaign
     *
     * @param Campaign $campaign
     * @return \Iterator
     */
    protected function createSubordinateIterator($campaign)
    {
        if (!$campaign instanceof Campaign) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Instance of %s is expected, %s given.',
                    'OroCRM\\Bundle\\MailChimpBundle\\Entity\\Campaign',
                    is_object($campaign) ? get_class($campaign) : gettype($campaign)
                )
            );
        }

        $parameters = $this->parameters;
        $parameters['id'] = $campaign->getOriginId();

        return $this->createExportMemberIterator($campaign, $parameters);
    }

    /**
     * @param Campaign $campaign
     * @param array $parameters
     * @return \Iterator
     */
    protected function createExportMemberIterator(Campaign $campaign, $parameters)
    {
        return new \CallbackFilterIterator(
            $this->createExportIterator(MailChimpClient::EXPORT_CAMPAIGN_SUBSCRIBER_ACTIVITY, $parameters),
            function (&$current) use ($campaign, $parameters) {
                $current[self::CAMPAIGN_KEY] = $campaign->getId();

                return true;
            }
        );
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
