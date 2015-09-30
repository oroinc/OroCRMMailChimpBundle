<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\ORM\Query;

abstract class AbstractInsertFromSelectWriter extends AbstractNativeQueryWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $qb = $this->getQueryBuilder($item);

            if ($this instanceof CleanUpInterface) {
                $this->cleanUp($item);
            }

            $this->executeInsertFromSelect($this->getInsert(), $qb->getQuery());
        }
    }

    /**
     * @param string $insert
     * @param Query $selectQuery
     */
    protected function executeInsertFromSelect($insert, Query $selectQuery)
    {
        $this->getEntityManager()->getConnection()->executeQuery(
            sprintf('%s %s', $insert, $selectQuery->getSQL()),
            $this->getQuerySqlParameters($selectQuery)
        );
    }

    /**
     * @return string
     */
    abstract protected function getInsert();
}
