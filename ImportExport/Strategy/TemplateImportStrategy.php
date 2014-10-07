<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use OroCRM\Bundle\MailChimpBundle\Entity\Template;

class TemplateImportStrategy extends ConfigurableAddOrReplaceStrategy implements
    StepExecutionAwareInterface,
    LoggerAwareInterface
{
    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Template $entity
     * @return Template
     */
    protected function beforeProcessEntity($entity)
    {
        if ($this->logger) {
            $this->logger->info('Syncing MailChimp Template [origin_id=' . $entity->getOriginId() . ']');
        }
        return parent::beforeProcessEntity($entity);
    }

    /**
     * @param Template $entity
     * @return Template
     */
    protected function afterProcessEntity($entity)
    {
        $jobContext = $this->getJobContext();
        $processedEntities = (array)$jobContext->get('processed_entities');
        $processedEntities[] = $entity->getOriginId();
        $jobContext->put('processed_entities', $processedEntities);

        return $entity;
    }

    /**
     * @return ExecutionContext
     */
    protected function getJobContext()
    {
        /** @var JobExecution $jobExecution */
        $jobExecution = $this->stepExecution->getJobExecution();
        return $jobExecution->getExecutionContext();
    }
}
