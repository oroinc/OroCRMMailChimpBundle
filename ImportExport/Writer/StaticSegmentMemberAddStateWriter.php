<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;

class StaticSegmentMemberAddStateWriter implements ItemWriterInterface
{
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
     * @param string $entityName
     */
    public function __construct(ManagerRegistry $registry, $entityName)
    {
        $this->registry = $registry;
        $this->entityName = $entityName;
    }

    /**
     * @param QueryBuilder[] $items
     *
     * {@inheritdoc}
     */
    public function write(array $items)
    {

        $insert = 'INSERT INTO orocrm_mc_static_segment_mmbr(member_id, static_segment_id, state)';

        foreach ($items as $qb) {
            $this->executeInsertFromSelect($insert, $qb->getQuery());
        }
    }

    /**
     * @param string $insert
     * @param Query $selectQuery
     */
    protected function executeInsertFromSelect($insert, Query $selectQuery)
    {
        $rsm = new ResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery($insert . ' ' . $selectQuery->getSQL(), $rsm);
        $query->execute($this->getQuerySqlParameters($selectQuery));
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
}
