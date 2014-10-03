<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;

class ProcessedEntities extends AbstractReader
{
    /**
     * {@inheritdoc}
     */
    public function read()
    {
        /** @var JobExecution $jobExecution */
        $jobExecution = $this->stepExecution->getJobExecution();
        $processedEntities = $jobExecution->getExecutionContext()->get('processed_entities');
        $jobExecution->getExecutionContext()->put('processed_entities', null);

        return $processedEntities;
    }
}
