<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

class StaticSegmentMemberToRemoveWriter extends AbstractInsertFromSelectWriter implements CleanUpInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getInsert()
    {
        return 'INSERT INTO orocrm_mc_tmp_mmbr_to_remove(member_id, static_segment_id, state)';
    }

    /**
     * {@inheritdoc}
     */
    public function cleanUp(array $item)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->entityName, 'e')
            ->where($qb->expr()->eq('IDENTITY(e.staticSegment)', ':staticSegment'))
            ->setParameter('staticSegment', $item['static_segment_id']);

        $qb->getQuery()->execute();
    }
}
