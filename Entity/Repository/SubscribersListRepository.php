<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

class SubscribersListRepository extends EntityRepository
{
    /**
     * Gets buffered query result iterator for all subscriber lists
     *
     * @return \Iterator
     */
    public function getAllSubscribersListIterator()
    {
        $queryBuilder = $this
            ->createQueryBuilder('subscribersList')
            ->select('subscribersList');

        return new BufferedQueryResultIterator($queryBuilder);
    }

    /**
     * Gets buffered query result iterator for all subscriber lists with segments
     *
     * @return \Iterator
     */
    public function getUsedSubscribersListIterator()
    {
        $queryBuilder = $this
            ->createQueryBuilder('subscribersList')
            ->select('subscribersList')
            ->join(
                'OroCRMMailChimpBundle:StaticSegment',
                'staticSegment',
                Join::WITH,
                'staticSegment.subscribersList = subscribersList.id'
            );

        return new BufferedQueryResultIterator($queryBuilder);
    }
}
