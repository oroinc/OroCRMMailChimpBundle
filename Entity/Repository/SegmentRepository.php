<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;

class SegmentRepository extends EntityRepository
{
    /**
     * @return \Iterator
     */
    public function getSegmentsWithDynamicMarketingList()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select('segment')
            ->from('OroCRMMailChimpBundle:Segment', 'segment')
            ->leftJoin('segment.marketingList', 'ml')
            ->where($qb->expr()->eq('ml.type', ':type'))
            ->setParameter('type', MarketingListType::TYPE_DYNAMIC, Type::STRING);

        return new BufferedQueryResultIterator($qb);
    }
}
