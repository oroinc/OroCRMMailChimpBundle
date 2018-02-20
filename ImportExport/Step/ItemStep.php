<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\ItemStep as BaseItemStep;
use Oro\Bundle\ImportExportBundle\Job\Step\AddToJobSummaryStepTrait;

class ItemStep extends BaseItemStep
{
    use AddToJobSummaryStepTrait;

    /** @var StepExecutor */
    protected $stepExecutor;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->stepExecutor = new StepExecutor();
    }

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution)
    {
        $this->initializeStepElements($stepExecution);

        $this->stepExecutor
            ->setReader($this->reader)
            ->setProcessor($this->processor)
            ->setWriter($this->writer);

        if (null !== $this->batchSize) {
            $this->stepExecutor->setBatchSize($this->batchSize);
        }

        $this->stepExecutor->execute($this);
        $this->flushStepElements();
    }

    /**
     * @param StepExecution $stepExecution
     */
    protected function initializeStepElements(StepExecution $stepExecution)
    {
        parent::initializeStepElements($stepExecution);
        $this->addToJobSummaryToStepExecution($stepExecution);
    }
}
