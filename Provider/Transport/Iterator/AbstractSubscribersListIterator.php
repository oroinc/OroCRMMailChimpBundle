<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

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
     * @param MailChimpClient $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
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
                    'OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList',
                    is_object($subscribersList) ? get_class($subscribersList) : gettype($subscribersList)
                )
            );
        }
    }
}
