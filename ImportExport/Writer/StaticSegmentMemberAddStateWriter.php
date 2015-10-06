<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentMemberAddStateWriter extends AbstractInsertFromSelectWriter implements CleanUpInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getInsert()
    {
        return 'INSERT INTO orocrm_mc_static_segment_mmbr(member_id, static_segment_id, state)';
    }

    /**
     * {@inheritdoc}
     */
    public function cleanUp(array $item)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->entityName, 'e')
            ->where($qb->expr()->eq('IDENTITY(e.staticSegment)', ':staticSegment'))
            ->andWhere($qb->expr()->neq('e.state', ':state'))
            ->setParameter('staticSegment', $item['static_segment_id'])
            ->setParameter('state', StaticSegmentMember::STATE_SYNCED);

        $qb->getQuery()->execute();
    }
}
