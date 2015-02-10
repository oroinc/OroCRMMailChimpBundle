<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;

class MemberSyncIterator extends AbstractStaticSegmentIterator
{
    /**
     * @var StaticSegment
     */
    protected $staticSegment;

    /**
     * @param StaticSegment $staticSegment
     *
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $this->staticSegment = $staticSegment;

        $qb = $this->getIteratorQueryBuilder($this->staticSegment);

        $qb->andWhere($qb->expr()->isNull(self::MEMBER_ALIAS));

        $bufferedIterator = new BufferedQueryResultIterator($qb);
        $bufferedIterator->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->setIsReverse(true);

        return $bufferedIterator;
    }

    /**
     * {@inheritdoc}
     */
    protected function read()
    {
        $result = parent::read();

        if (!$result) {
            return null;
        }

        return [
            $result,
            'subscribersList_id' => $this->staticSegment->getSubscribersList()->getId(),
            'channel_id'         => $this->staticSegment->getChannel()->getId(),
            'entityClass'        => $this->staticSegment->getMarketingList()->getEntity(),
        ];
    }
}
