<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

class MemberWriter extends AbstractExportWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $item = reset($items);

        $this->transport->init($item['channel']);
    }
}
