<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentMemberRemoveStateIterator extends AbstractStaticSegmentIterator
{
    /**
     * @var string
     */
    protected $segmentMemberClassName;

    /**
     * @param string $segmentMemberClassName
     */
    public function setSegmentMemberClassName($segmentMemberClassName)
    {
        $this->segmentMemberClassName = $segmentMemberClassName;
    }

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
        $qb       = $this->getIteratorQueryBuilder($staticSegment);
        $identity = self::MEMBER_ALIAS . '.id';
        $memberId = 'IDENTITY(segmentMember.member)';

        $qb->select($identity)
            ->andWhere($qb->expr()->isNotNull($identity));

        $segmentMembersQb = clone $qb;
        $segmentMembersQb
            ->resetDQLParts()
            ->select(
                [
                    'IDENTITY(segmentMember.staticSegment) as static_segment_id',
                    $memberId . ' as member_id',
                    $segmentMembersQb->expr()->literal(StaticSegmentMember::STATE_REMOVE) . ' state'
                ]
            )
            ->from($this->segmentMemberClassName, 'segmentMember')
            ->andWhere($qb->expr()->eq('IDENTITY(segmentMember.staticSegment)', $staticSegment->getId()));

        $qb->andWhere($qb->expr()->eq($memberId, $identity));

        $segmentMembersQb->andWhere(
            $segmentMembersQb->expr()->not(
                $segmentMembersQb->expr()->exists($qb->getDQL())
            )
        );

        $bufferedIterator = new BufferedQueryResultIterator($segmentMembersQb);
        $bufferedIterator->setReverse(true);
        $bufferedIterator->setBufferSize(self::BUFFER_SIZE);

        return $bufferedIterator;
    }
}
