<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;
use Oro\Bundle\IntegrationBundle\Exception\InvalidConfigurationException;

abstract class AbstractIteratorBasedReader extends AbstractReader
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
     * @var \Iterator
     */
    protected $sourceIterator;

    /**
     * @var bool
     */
    protected $rewound = false;

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

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->getSourceIterator()) {
            throw new LogicException('Reader must be configured with source');
        }
        if (!$this->rewound) {
            $this->sourceIterator->rewind();
            $this->rewound = true;
        }

        $result = null;
        if ($this->sourceIterator->valid()) {
            $result = $this->sourceIterator->current();
            $context = $this->getContext();
            $context->incrementReadOffset();
            $context->incrementReadCount();
            $this->sourceIterator->next();
        }

        return $result;
    }

    /**
     * Setter for iterator
     *
     * @param \Iterator $sourceIterator
     */
    public function setSourceIterator(\Iterator $sourceIterator = null)
    {
        $this->sourceIterator = $sourceIterator;
        $this->rewound = false;
    }

    /**
     * Getter for iterator
     *
     * @return \Iterator|null
     */
    public function getSourceIterator()
    {
        return $this->sourceIterator;
    }
}
