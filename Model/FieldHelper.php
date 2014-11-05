<?php

namespace OroCRM\Bundle\MailChimpBundle\Model;

use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface;

class FieldHelper
{
    /**
     * @var VirtualFieldProviderInterface
     */
    protected $virtualFieldProvider;

    /**
     * @param VirtualFieldProviderInterface $virtualFieldProvider
     */
    public function __construct(VirtualFieldProviderInterface $virtualFieldProvider)
    {
        $this->virtualFieldProvider = $virtualFieldProvider;
    }

    /**
     * @param string $entityClass
     * @param QueryBuilder $qb
     * @param string $fieldName
     * @return string
     */
    public function getFieldExpr($entityClass, QueryBuilder $qb, $fieldName)
    {
        if ($this->virtualFieldProvider->isVirtualField($entityClass, $fieldName)) {
            return $this->getVirtualFieldExpression($qb, $entityClass, $fieldName);
        } else {
            return sprintf('%s.%s', $this->getRootTableAlias($qb), $fieldName);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @return string
     */
    protected function getRootTableAlias(QueryBuilder $qb)
    {
        $fromParts = $qb->getDQLPart('from');
        /** @var From $from */
        $from = reset($fromParts);

        return $from->getAlias();
    }

    /**
     * @param QueryBuilder $qb
     * @param string $entityClass
     * @param string $fieldName
     * @return string
     */
    protected function getVirtualFieldExpression(QueryBuilder $qb, $entityClass, $fieldName)
    {
        $rootAlias = $this->getRootTableAlias($qb);
        $conditions = ['entity' => $rootAlias];

        $fieldConfig = $this->virtualFieldProvider->getVirtualFieldQuery($entityClass, $fieldName);
        $fieldConfigJoins = $fieldConfig['join'];

        $joins = $qb->getDQLPart('join');

        if (empty($joins)) {
            foreach ($fieldConfigJoins as $type => $typedFieldConfigJoins) {
                foreach ($typedFieldConfigJoins as $typedFieldConfigJoin) {
                    $join = new Join(
                        $type,
                        $typedFieldConfigJoin['join'],
                        $typedFieldConfigJoin['alias'],
                        $typedFieldConfigJoin['conditionType'],
                        $typedFieldConfigJoin['condition']
                    );

                    $qb->add('join', [$rootAlias => $join], true);

                    $joins[$rootAlias][] = $join;
                }
            }
        }

        /** @var Join $join */
        foreach ($joins[$rootAlias] as $join) {
            $joinType = strtolower($join->getJoinType());
            if (array_key_exists($joinType, $fieldConfigJoins)) {
                foreach ($fieldConfigJoins[$joinType] as $fieldJoin) {
                    if (strtoupper($fieldJoin['conditionType']) == strtoupper($join->getConditionType())) {
                        $fixedJoin = $this->replaceAliases($conditions, $fieldJoin['join']);

                        if ($fixedJoin == $join->getJoin()) {
                            $conditions[$fieldJoin['alias']] = $join->getAlias();
                            $fixedCondition = $this->replaceAliases($conditions, $fieldJoin['condition']);

                            if ($fixedCondition != (string)$join->getCondition()) {
                                unset($conditions[$fieldJoin['alias']]);
                            }
                        }
                    }
                }
            }
        }

        return $this->replaceAliases($conditions, $fieldConfig['select']['expr']);
    }

    /**
     * @param array $aliasMapping
     * @param string $data
     * @return string
     */
    protected function replaceAliases(array $aliasMapping, $data)
    {
        foreach ($aliasMapping as $search => $replace) {
            $data = str_replace($search . '.', $replace . '.', $data);
        }

        return $data;
    }
}
