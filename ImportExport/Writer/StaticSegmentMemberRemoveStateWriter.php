<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\ORM\Query\ResultSetMapping;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\StaticSegmentMemberToRemoveIterator;

class StaticSegmentMemberRemoveStateWriter extends AbstractNativeQueryWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $qb = $this->getQueryBuilder($item);
            $selectQuery = $qb->getQuery();
            $staticSegmentId = $item[StaticSegmentMemberToRemoveIterator::STATIC_SEGMENT_ID];

            $query = $this->getEntityManager()->createNativeQuery(
                sprintf(
                    "UPDATE orocrm_mc_static_segment_mmbr
                        SET state = '%s'
                        WHERE
                          member_id IN (%s)
                          AND static_segment_id = %d",
                    StaticSegmentMember::STATE_REMOVE,
                    $selectQuery->getSQL(),
                    $staticSegmentId
                ),
                new ResultSetMapping()
            );
            $query->execute($this->getQuerySqlParameters($selectQuery));
        }
    }
}
