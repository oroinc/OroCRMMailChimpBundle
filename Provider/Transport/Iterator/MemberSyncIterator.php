<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;

class MemberSyncIterator extends AbstractStaticSegmentIterator
{
    /**
     * @param StaticSegment $staticSegment
     *
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $qb = $this->getIteratorQueryBuilder($staticSegment);

        $qb
            ->addSelect(
                [
                    $staticSegment->getSubscribersList()->getId() . ' subscribersList_id',
                    $staticSegment->getChannel()->getId() . ' channel_id',
                ]
            )
            ->andWhere($qb->expr()->isNull(self::MEMBER_ALIAS));

        return new BufferedQueryResultIterator($qb);
    }
}
