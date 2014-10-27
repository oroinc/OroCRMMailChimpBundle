<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class StaticSegmentListIterator extends AbstractSubordinateIterator
{
    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param \Iterator $subscribersLists
     * @param MailChimpClient $client
     * @param array $parameters
     */
    public function __construct(\Iterator $subscribersLists, MailChimpClient $client, array $parameters = [])
    {
        parent::__construct($subscribersLists);
        $this->client = $client;
        $this->parameters = $parameters;
    }

    /**
     * @param SubscribersList $subscribersList
     * @return \Iterator
     */
    protected function createSubordinateIterator($subscribersList)
    {
        if (!$subscribersList instanceof SubscribersList) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Instance of %s is expected, %s given.',
                    'OroCRM\\Bundle\\MailChimpBundle\\Entity\\SubscribersList',
                    is_object($subscribersList) ? get_class($subscribersList) : gettype($subscribersList)
                )
            );
        }

        $segmentIterator = new StaticSegmentIterator($this->client);
        $segmentIterator->setListId($subscribersList->getOriginId());

        return $segmentIterator;
    }
}
