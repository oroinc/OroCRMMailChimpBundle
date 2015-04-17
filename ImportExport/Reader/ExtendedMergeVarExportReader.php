<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\ExtendedMergeVarExportIterator;

class ExtendedMergeVarExportReader extends AbstractExtendedMergeVarExportReader
{
    /**
     * @var string
     */
    protected $extendedMergeVarClassName;

    /**
     * @param string $extendedMergeVarClassName
     */
    public function setExtendedMergeVarClassName($extendedMergeVarClassName)
    {
        $this->extendedMergeVarClassName = $extendedMergeVarClassName;
    }

    /**
     * @param Channel $channel
     * @return ExtendedMergeVarExportIterator
     * @throws \InvalidArgumentException if ExtendedMergeVar class name is not provided
     */
    protected function getExtendedMergeVarIterator(Channel $channel)
    {
        if (!is_string($this->extendedMergeVarClassName) || empty($this->extendedMergeVarClassName)) {
            throw new \InvalidArgumentException('ExtendedMergeVar class name must be provided.');
        }

        $iterator = new ExtendedMergeVarExportIterator(
            $this->getSegmentsIterator($channel),
            $this->doctrineHelper,
            $this->extendedMergeVarClassName
        );
        return $iterator;
    }
}
