<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class StaticSegmentMemberUnsubscribeStateIterator extends AbstractStaticSegmentIterator
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
                    $segmentMembersQb->expr()->literal(StaticSegmentMember::STATE_UNSUBSCRIBE) . ' state'
                ]
            )
            ->from($this->segmentMemberClassName, 'segmentMember')
            ->join('segmentMember.member', 'smmb')
            ->join('segmentMember.staticSegment', 'staticSegment')
            ->andWhere($qb->expr()->eq('staticSegment.id', $staticSegment->getId()))
            ->andWhere($segmentMembersQb->expr()->In('smmb.id', $qb->getDQL()));

        $bufferedIterator = new BufferedQueryResultIterator($segmentMembersQb);
        $bufferedIterator->setReverse(true);

        return $bufferedIterator;
    }

    /**
     * @param QueryBuilder $qb
     */
    protected function prepareIteratorPart(QueryBuilder $qb)
    {
        $from = $qb->getDQLPart('from');
        $entityAlias = $from[0]->getAlias();

        $qb
            ->leftJoin(
                'OroCRMMarketingListBundle:MarketingListUnsubscribedItem',
                'mlu',
                Join::WITH,
                "mlu.entityId = $entityAlias"
            )
            ->andWhere('mlu.id IS NOT NULL');
    }
}
