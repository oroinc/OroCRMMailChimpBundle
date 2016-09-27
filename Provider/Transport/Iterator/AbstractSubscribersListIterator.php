<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

abstract class AbstractSubscribersListIterator extends AbstractSubordinateIterator
{
    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param \Iterator $mainIterator
     * @param MailChimpClient $client
     */
    public function __construct(
        \Iterator $mainIterator,
        MailChimpClient $client
    ) {
        $this->mainIterator = $mainIterator;
        $this->client = $client;
    }

    /**
     * @param mixed $subscribersList
     */
    protected function assertSubscribersList($subscribersList)
    {
        if (!$subscribersList instanceof SubscribersList) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Instance of %s is expected, %s given.',
                    'Oro\Bundle\MailChimpBundle\Entity\SubscribersList',
                    is_object($subscribersList) ? get_class($subscribersList) : gettype($subscribersList)
                )
            );
        }
    }
}
