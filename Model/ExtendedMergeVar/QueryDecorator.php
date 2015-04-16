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

            $columnWithTableAlias = $exprParts[0];

            $alias = $rootAlias;
            $columnName = $columnWithTableAlias;

            if (strpos($columnWithTableAlias, '.')) {
                list($alias, $columnName) = explode('.', $columnWithTableAlias);
            }

            $columnAlias = $columnName;

            if (isset($exprParts[2])) {
                $columnAlias = $exprParts[2];
            }

            if ($alias == $rootAlias && $columnName == 'id') {
                continue;
            }

            $queryBuilder->addSelect($each);
            $queryBuilder->addSelect(sprintf('%s as %s_%s', $columnWithTableAlias, $columnAlias, $columnName));
        }
    }
}
