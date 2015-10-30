<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\ORM\Query\ResultSetMapping;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\StaticSegmentMemberToRemoveIterator;

class StaticSegmentMemberStateWriter extends AbstractNativeQueryWriter
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
            $state = $item[StaticSegmentMemberToRemoveIterator::STATE];

            $updateQuery = sprintf(
                "UPDATE orocrm_mc_static_segment_mmbr
                    SET state = '%s'
                    WHERE
                      member_id IN (%s)
                      AND static_segment_id = %d",
                $state,
                $selectQuery->getSQL(),
                $staticSegmentId
            );

            $rsm = new ResultSetMapping();
            $query = $this->getEntityManager()->createNativeQuery($updateQuery, $rsm);
            $query->execute($this->getQuerySqlParameters($selectQuery));
        }
    }
}
