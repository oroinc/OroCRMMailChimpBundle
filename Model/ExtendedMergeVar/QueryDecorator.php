<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar;

use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;

class QueryDecorator
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function decorate(QueryBuilder $queryBuilder)
    {
        /** @var Select[] $selects */
        $selects = $queryBuilder->getDQLPart('select');

        foreach ($selects as $select) {

            foreach ($this->extractSelectParts($select) as $each) {

                $each = preg_replace('/ as /i', ' as ', $each);

                if (!strpos($each, ' as ')) {
                    continue;
                }

                list($field, $alias) = explode(' as ', $each);

                $tableAlias = null;
                $columnName = $field;

                if (strpos($field, '.')) {
                    list($tableAlias, $columnName) = explode('.', $field, 2);
                }

                if ($tableAlias && $columnName && $alias) {
                    $queryBuilder->addSelect(
                        sprintf('%s.%s as %s_%s', $tableAlias, $columnName, $alias, $columnName)
                    );
                }
            }
        }
    }

    /**
     * @param Select $select
     * @return array
     */
    protected function extractSelectParts(Select $select)
    {
        $select = (string) $select;
        if (!strpos($select, ',')) {
            return [$select];
        }

        $selects = [];
        $parts = explode(',', $select);
        foreach ($parts as $part) {
            $selects[] = trim($part);
        }
        return $selects;
    }
}
