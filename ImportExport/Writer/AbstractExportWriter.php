<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;
use Psr\Log\LoggerInterface;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

abstract class AbstractExportWriter extends PersistentBatchWriter implements ItemWriterInterface
{
    /**
     * @var TransportInterface|MailChimpTransport
     */
    protected $transport;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
