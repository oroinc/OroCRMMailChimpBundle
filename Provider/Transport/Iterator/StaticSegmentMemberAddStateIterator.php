<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\MarketingListQueryBuilderAdapter;

class StaticSegmentMemberAddStateIterator extends AbstractStaticSegmentIterator
{
    /**
     * @param StaticSegment $staticSegment
     *
     * @return \Iterator|BufferedQueryResultIterator
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $qb = $this->getIteratorQueryBuilder($staticSegment);
        $alias = sprintf('%s.id', MarketingListQueryBuilderAdapter::MEMBER_ALIAS);

        $qb
            ->select(
                [
                    MarketingListQueryBuilderAdapter::MEMBER_ALIAS . '.id member_id',
                    $staticSegment->getId() . ' static_segment_id',
                    $qb->expr()->literal(StaticSegmentMember::STATE_ADD) . ' state'
                ]
            )
            ->leftJoin(
                sprintf('%s.segmentMembers', MarketingListQueryBuilderAdapter::MEMBER_ALIAS),
                'segmentMembers',
                Join::WITH,
                $qb->expr()->eq('segmentMembers.staticSegment', $staticSegment->getId())
            )
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->isNull('segmentMembers'),
                    $qb->expr()->isNotNull(sprintf('%s.originId', MarketingListQueryBuilderAdapter::MEMBER_ALIAS)),
                    $qb->expr()
                        ->eq(
                            sprintf('%s.subscribersList', MarketingListQueryBuilderAdapter::MEMBER_ALIAS),
                            ':subscribersList'
                        )
                )
            )
            ->setParameter('subscribersList', $staticSegment->getSubscribersList())
            ->orderBy($alias)
            ->groupBy($alias);

        $bufferedIterator = new BufferedQueryResultIterator($qb);
        $bufferedIterator->setReverse(true);

        return $bufferedIterator;
    }
}
