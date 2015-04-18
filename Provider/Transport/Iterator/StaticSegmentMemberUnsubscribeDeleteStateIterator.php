<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentMemberUnsubscribeDeleteStateIterator extends AbstractStaticSegmentIterator
{
    /**
     * @param StaticSegment $staticSegment
     *
     * @return \Iterator|BufferedQueryResultIterator
     */
    protected function createSubordinateIterator($staticSegment)
    {
        if (!$this->segmentMemberClassName) {
            throw new \InvalidArgumentException('StaticSegmentMember class name must be provided');
        }

        $qb = $this
            ->getIteratorQueryBuilder($staticSegment)
            ->select(self::MEMBER_ALIAS . '.id');

        $segmentMembersQb = clone $qb;
        $segmentMembersQb
            ->resetDQLParts()
            ->select(
                [
                    'staticSegment.id static_segment_id',
                    'smmb.id member_id',
                    $segmentMembersQb->expr()->literal(StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE) . ' state'
                ]
            )
            ->from($this->segmentMemberClassName, 'segmentMember')
            ->join('segmentMember.member', 'smmb')
            ->join('segmentMember.staticSegment', 'staticSegment')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('staticSegment.id', $staticSegment->getId()),
                    $segmentMembersQb->expr()->notIn('smmb.id', $qb->getDQL())
                )
            );

        $bufferedIterator = new BufferedQueryResultIterator($segmentMembersQb);
        $bufferedIterator->setReverse(true);

        return $bufferedIterator;
    }

    protected function prepareIteratorPart(QueryBuilder $qb)
    {
        return;
    }
}
