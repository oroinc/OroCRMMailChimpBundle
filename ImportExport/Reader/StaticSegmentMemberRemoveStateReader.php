<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

class StaticSegmentMemberRemoveStateReader extends AbstractMarketingListReader
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
     * {@inheritdoc}
     */
    protected function getQueryIterator()
    {
        $qb = $this
            ->getIteratorQueryBuilder($this->marketingList)
            ->select(self::MEMBER_ALIAS . '.id');

        $segmentMembersQb = clone $qb;
        $segmentMembersQb
            ->resetDQLParts()
            ->select('segmentMembersToRemove')
            ->from($this->segmentMemberClassName, 'segmentMembersToRemove')
            ->join(
                $this->memberClassName,
                'membersToRemove',
                Join::WITH,
                'segmentMembersToRemove.member = membersToRemove.id'
            )
            ->andWhere($qb->expr()->eq('segmentMembersToRemove.staticSegment', $this->segment->getId()))
            ->andWhere($segmentMembersQb->expr()->notIn('membersToRemove.id', $qb->getDQL()));

        return new BufferedQueryResultIterator($segmentMembersQb);
    }
}
