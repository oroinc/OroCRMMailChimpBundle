<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

class StaticSegmentMemberAddStateReader extends AbstractMarketingListReader
{
    /**
     * {@inheritdoc}
     */
    protected function getQueryIterator()
    {
        $qb = $this->getIteratorQueryBuilder($this->marketingList);

        $qb
            ->select(self::MEMBER_ALIAS)
            ->leftJoin(
                sprintf('%s.segmentMembers', self::MEMBER_ALIAS),
                'segmentMembers',
                Join::WITH,
                $qb->expr()->eq('segmentMembers.staticSegment', $this->segment->getId())
            )
            ->andWhere($qb->expr()->isNull('segmentMembers'));

        return new BufferedQueryResultIterator($qb);
    }
}
