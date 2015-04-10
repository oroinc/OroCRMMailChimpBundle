<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar;

use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;

class QueryDecorator
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function decorate(QueryBuilder $queryBuilder)
    {
        /** @var Select[] $selects */
        $selects = $queryBuilder->getDQLPart('select');
        $selectParts = [];
        foreach ($selects as $select) {
            $parts = $select->getParts();
            $selectParts = array_merge($selectParts, $parts);
        }
        $queryBuilder->resetDQLPart('select');
        $rootAliases = $queryBuilder->getRootAliases();
        $rootAlias = reset($rootAliases);
        foreach ($selectParts as $each) {

            $exprParts = explode(' ', $each);

            $columnWithAlias = $exprParts[0];

            $alias = $rootAlias;
            $columnName = $columnWithAlias;

            if (strpos($columnWithAlias, '.')) {
                list($alias, $columnName) = explode('.', $columnWithAlias);
            }

            if ($alias == $rootAlias && $columnName == 'id') {
                continue;
            }

            $queryBuilder->addSelect($each);
            $queryBuilder->addSelect(sprintf('%s', $columnWithAlias));
        }
    }
}
