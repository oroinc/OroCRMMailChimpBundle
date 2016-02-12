<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use Oro\Bundle\BatchBundle\Step\ItemStep as BaseItemStep;

class ItemStep extends BaseItemStep
{
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
}
