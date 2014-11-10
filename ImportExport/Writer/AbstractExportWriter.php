<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use Psr\Log\LoggerInterface;

use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\UIBundle\Tools\ArrayUtils;
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

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        if (!$this->transport) {
            throw new \InvalidArgumentException('Transport was not provided');
        }

        return parent::write($items);
    }

    /**
     * @param array $response
     * @param string $container
     * @param string|null $key
     *
     * @return array
     */
    protected function getArrayData(array $response, $container, $key = null)
    {
        if (!empty($response[$container])) {
            if ($key) {
                return ArrayUtils::arrayColumn($response[$container], $key);
            }

            return $response[$container];
        }

        return [];
    }
}
