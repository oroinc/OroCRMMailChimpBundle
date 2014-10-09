<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

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
        $queryBuilder = $this->createQueryBuilder('list')->select('list');
        $result = new BufferedQueryResultIterator($queryBuilder);
        return $result;
    }
}
