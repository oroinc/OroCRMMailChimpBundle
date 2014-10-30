<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

class StaticSegmentExportWriter extends AbstractExportWriter
{
    /**
     * @var MailChimpTransport
     */
    protected $transport;

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
    }
}
