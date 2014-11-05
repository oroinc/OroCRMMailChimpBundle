<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentMemberAddStateIterator extends AbstractStaticSegmentIterator
{
    /**
     * @param StaticSegment $staticSegment
     *
     * @return \Iterator|BufferedQueryResultIterator
     */
    protected function createSubordinateIterator($staticSegment)
    {
        if (!$marketingList = $staticSegment->getMarketingList()) {
            return new \ArrayIterator();
        }

        $qb = $this->getIteratorQueryBuilder($marketingList);

        $qb
            ->select(
                [
                    self::MEMBER_ALIAS . '.id member_id',
                    $staticSegment->getId() . ' static_segment_id',
                    $qb->expr()->literal(StaticSegmentMember::STATE_ADD) . ' state'
                ]
            )
            ->leftJoin(
                sprintf('%s.segmentMembers', self::MEMBER_ALIAS),
                'segmentMembers',
                Join::WITH,
                $qb->expr()->eq('segmentMembers.staticSegment', $staticSegment->getId())
            )
            ->andWhere($qb->expr()->isNull('segmentMembers'))
            ->andWhere($qb->expr()->isNotNull(sprintf('%s.originId', self::MEMBER_ALIAS)));

        return new BufferedQueryResultIterator($qb);
    }
}
