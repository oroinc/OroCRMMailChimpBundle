<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;

abstract class AbstractIteratorBasedReader extends IteratorBasedReader
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }
}
