<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use Oro\Bundle\IntegrationBundle\Exception\InvalidConfigurationException;

abstract class AbstractIteratorBasedReader extends IteratorBasedReader
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $channelClassName;

    /**
     * @param ContextRegistry $contextRegistry
     * @param DoctrineHelper $doctrineHelper
     * @param string $channelClassName
     */
    public function __construct(ContextRegistry $contextRegistry, DoctrineHelper $doctrineHelper, $channelClassName)
    {
        parent::__construct($contextRegistry);

        $this->doctrineHelper = $doctrineHelper;
        $this->channelClassName = $channelClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->channelClassName) {
            throw new InvalidConfigurationException('Channel class name must be provided');
        }
    }
}
