<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

class StaticSegmentListIterator extends AbstractSubscribersListIterator
{
    /**
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($subscribersList)
    {
        parent::assertSubscribersList($subscribersList);

        $segmentIterator = new StaticSegmentIterator($this->client);
        $segmentIterator->setSubscriberListId($subscribersList->getOriginId());

        return $segmentIterator;
    }
}
