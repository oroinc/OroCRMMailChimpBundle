<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Processor;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface;

class StaticSegmentRemoveProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if (!empty($item['member_id'])) {
            return $item['member_id'];
        }

        return null;
    }
}
