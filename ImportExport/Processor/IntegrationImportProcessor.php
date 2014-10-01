<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Processor;

use Oro\Bundle\ImportExportBundle\Processor\ImportProcessor;

class IntegrationImportProcessor extends ImportProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $item['channel:id'] = $this->context->getOption('channel');

        return parent::process($item);
    }
}
