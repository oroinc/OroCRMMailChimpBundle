<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentMemberAddStateIterator extends AbstractStaticSegmentMemberStateIterator
{
    /**
     * @param StaticSegment $staticSegment
     *
     * @return \Iterator|BufferedQueryResultIterator
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $qb = $this->getIteratorQueryBuilder($staticSegment);

        $qb
            ->resetDQLParts()
            ->setParameters(['staticSegment' => $staticSegment->getId()])
            ->select(
                [
                    self::MEMBER_ALIAS . '.id member_id',
                    $staticSegment->getId() . ' static_segment_id',
                    $qb->expr()->literal(StaticSegmentMember::STATE_ADD) . ' state'
                ]
            )
            ->from($this->memberClassName, self::MEMBER_ALIAS)
            ->leftJoin(
                $this->segmentMemberClassName,
                'segmentMember',
                Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->eq(sprintf('%s.id', self::MEMBER_ALIAS), 'segmentMember.member'),
                    $qb->expr()->eq('segmentMember.staticSegment', ':staticSegment')
                )
            )
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->isNull('segmentMember.id'),
                    $qb->expr()->isNotNull(sprintf('%s.originId', self::MEMBER_ALIAS))
                )
            );

        return new BufferedQueryResultIterator($qb);
    }
}
