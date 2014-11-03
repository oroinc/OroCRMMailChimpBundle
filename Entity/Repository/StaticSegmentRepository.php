<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;

class StaticSegmentRepository extends EntityRepository
{
    /**
     * @param array|null $segments
     * @return \Iterator
     */
    public function getStaticSegmentsWithDynamicMarketingList($segments = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select('staticSegment')
            ->from('OroCRMMailChimpBundle:StaticSegment', 'staticSegment');

        if ($segments) {
            $qb->andWhere('staticSegment.id IN(:segments)')
                ->setParameter('segments', $segments);
        } else {
            $qb
                ->leftJoin('staticSegment.marketingList', 'ml')
                ->where($qb->expr()->eq('ml.type', ':type'))
                ->setParameter('type', MarketingListType::TYPE_DYNAMIC, Type::STRING);
        }

        return new BufferedQueryResultIterator($qb);
    }
}
