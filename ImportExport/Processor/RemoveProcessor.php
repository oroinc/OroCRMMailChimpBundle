<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Processor\EntityNameAwareInterface;
use Oro\Bundle\ImportExportBundle\Processor\StepExecutionAwareProcessor;

class RemoveProcessor implements StepExecutionAwareProcessor, EntityNameAwareInterface
{
    /**
     * @var ContextRegistry
     */
    protected $contextRegistry;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $field;

    /**
     * @param ContextRegistry $contextRegistry
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        DoctrineHelper $doctrineHelper
    ) {
        $this->contextRegistry = $contextRegistry;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if ($item) {
            $this->updateContext($item);
        }

        return $item;
    }

    /**
     * @param array $item
     * @todo Delete count does not shown for second step because https://magecore.atlassian.net/browse/BAP-2600
     */
    protected function updateContext($item)
    {
        $context = $this->contextRegistry->getByStepExecution($this->stepExecution);
        $toDelete = (int)$context->getDeleteCount() + $this->getItemsToRemoveCount($item);
        $context->setValue('delete_count', $toDelete);
    }

    /**
     * @param array $items
     * @return int
     */
    protected function getItemsToRemoveCount($items)
    {
        $em = $this->doctrineHelper->getEntityManager($this->entityName);
        $identifierFieldName = $this->doctrineHelper->getSingleEntityIdentifierFieldName($this->entityName);
        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(e.' . $identifierFieldName . ') as itemsCount')
            ->from($this->entityName, 'e')
            ->andWhere($qb->expr()->notIn('e.' . $this->field, ':items'))
            ->setParameter('items', (array)$items);

        $result = $qb->getQuery()->getArrayResult();
        if ($result) {
            return (int)$result[0]['itemsCount'];
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * Set field name which will be used for search of entities to remove.
     *
     * @param string $field
     */
    public function setSearchField($field)
    {
        $this->field = $field;
    }
}
