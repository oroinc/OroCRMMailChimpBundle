<?php

namespace Oro\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;

class StaticSegmentRepository extends EntityRepository
{
    /**
     * @param array|null $segments
     * @param Channel|null $channel
     * @param bool $getAll
     * @return \Iterator
     */
    public function getStaticSegmentsToSync(array $segments = null, Channel $channel = null, $getAll = false)
    {
        $qb = $this->getStaticSegmentsQueryBuilder($segments, $channel);

        if (!$segments && !$getAll) {
            $qb
                ->leftJoin('staticSegment.marketingList', 'ml')
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->eq('ml.type', ':type'),
                        $qb->expr()->neq('staticSegment.syncStatus', ':status')
                    )
                )
                ->setParameter('type', MarketingListType::TYPE_DYNAMIC)
                ->setParameter('status', StaticSegment::STATUS_IN_PROGRESS);
        }

        return new BufferedQueryResultIterator($qb);
    }

    /**
     * @param array|null $segments
     * @param Channel|null $channel
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getStaticSegmentsQueryBuilder(array $segments = null, Channel $channel = null)
    {
        $qb = $this->createQueryBuilder('staticSegment');

        $qb->select('staticSegment');

        if ($segments) {
            $qb
                ->andWhere('staticSegment.id IN(:segments)')
                ->setParameter('segments', $segments);
        }

        if ($channel) {
            $qb
                ->andWhere($qb->expr()->eq('staticSegment.channel', ':channel'))
                ->setParameter('channel', $channel);
        }

        return $qb;
    }

    /**
     * @param Channel|null $channel
     * @param array|null $segments
     * @return BufferedQueryResultIterator
     */
    public function getStaticSegments(Channel $channel = null, array $segments = null)
    {
        return new BufferedQueryResultIterator($this->getStaticSegmentsQueryBuilder($segments, $channel));
    }
}
