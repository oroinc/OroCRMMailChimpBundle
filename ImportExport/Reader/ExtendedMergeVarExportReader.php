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
        if (!is_string($extendedMergeVarClassName) || empty($extendedMergeVarClassName)) {
            throw new \InvalidArgumentException('ExtendedMergeVar class name must be provided.');
        }
        $this->extendedMergeVarClassName = $extendedMergeVarClassName;
    }

    /**
     * @param Channel $channel
     * @return \Iterator
     */
    protected function getExtendedMergeVarIterator(Channel $channel)
    {
        $iterator = new ExtendedMergeVarExportIterator(
            $this->getSegmentsIterator($channel),
            $this->doctrineHelper,
            $this->extendedMergeVarClassName
        );
        return $iterator;
    }
}
