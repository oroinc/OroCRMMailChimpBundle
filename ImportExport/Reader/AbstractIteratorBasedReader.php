<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;

abstract class AbstractIteratorBasedReader extends IteratorBasedReader
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;


    /**
     * @param ContextRegistry $contextRegistry
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(ContextRegistry $contextRegistry, DoctrineHelper $doctrineHelper)
    {
        parent::__construct($contextRegistry);

        $this->doctrineHelper = $doctrineHelper;
    }
}
