<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Processor;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface;

class DummyProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return $item;
    }
}
