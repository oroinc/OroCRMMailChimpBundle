<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;

class StaticSegmentRepository extends EntityRepository
{
    /**
     * @param array|null $segments
     * @return \Iterator
     */
    public function getStaticSegmentsToSync(array $segments = null)
    {
        $qb = $this->createQueryBuilder('staticSegment');

        $qb->select('staticSegment');

        if ($segments) {
            $qb
                ->andWhere('staticSegment.id IN(:segments)')
                ->setParameter('segments', $segments);
        } else {
            $qb
                ->leftJoin('staticSegment.marketingList', 'ml')
                ->where($qb->expr()->eq('ml.type', ':type'))
                ->setParameter('type', MarketingListType::TYPE_DYNAMIC);
        }

        $qb
            ->andWhere($qb->expr()->neq('staticSegment.syncStatus', ':status'))
            ->setParameter('status', StaticSegment::STATUS_IN_PROGRESS);

        return new BufferedQueryResultIterator($qb);
    }
}
