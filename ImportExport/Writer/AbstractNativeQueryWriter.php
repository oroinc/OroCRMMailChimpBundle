<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractNativeQueryWriter implements ItemWriterInterface
{
    const QUERY_BUILDER = 'query_builder';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param string $entityName
     * @return AbstractNativeQueryWriter
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->registry->getManagerForClass($this->entityName);
        }

        return $this->em;
    }

    /**
     * Processes query parameter mappings and return SQL parameters.
     *
     * @param Query $query
     * @return array
     * @throws QueryException
     */
    protected function getQuerySqlParameters(Query $query)
    {
        $parser = new Parser($query);
        $parseResult = $parser->parse();
        $parametersMapping = $parseResult->getParameterMappings();
        $resultSetMapping = $parseResult->getResultSetMapping();

        $sqlParams = [];
        foreach ($query->getParameters() as $parameter) {
            $key = $parameter->getName();
            $value = $parameter->getValue();

            if (!isset($parametersMapping[$key])) {
                throw QueryException::unknownParameter($key);
            }

            if (isset($resultSetMapping->metadataParameterMapping[$key]) && $value instanceof ClassMetadata) {
                $value = $value->getMetadataValue($resultSetMapping->metadataParameterMapping[$key]);
            }

            $value = $query->processParameterValue($value);

            $sqlPositions = $parametersMapping[$key];

            // optimized multi value sql positions away for now,
            // they are not allowed in DQL anyways.
            $value = array($value);
            $countValue = count($value);

            for ($i = 0, $l = count($sqlPositions); $i < $l; $i++) {
                $sqlParams[$sqlPositions[$i]] = $value[($i % $countValue)];
            }
        }

        if ($sqlParams) {
            ksort($sqlParams);
            $sqlParams = array_values($sqlParams);
        }

        return $sqlParams;
    }

    /**
     * @param array $item
     * @return QueryBuilder
     * @throws \InvalidArgumentException
     */
    protected function getQueryBuilder($item)
    {
        if (!isset($item[self::QUERY_BUILDER]) || !$item[self::QUERY_BUILDER] instanceof QueryBuilder) {
            throw new \InvalidArgumentException(
                'Required query_builder parameter must be instance of QueryBuilder'
            );
        }

        return $item[self::QUERY_BUILDER];
    }
}
