<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\MmbrExtdMergeVarExportIterator;

class MemberExtendedMergeVarExportReader extends AbstractExtendedMergeVarExportReader
{
    /**
     * @var string
     */
    protected $mmbrExtdMergeVarClassName;

    /**
     * @param string $mmbrExtdMergeVarClassName
     */
    public function setMmbrExtdMergeVarClassName($mmbrExtdMergeVarClassName)
    {
        $this->mmbrExtdMergeVarClassName = $mmbrExtdMergeVarClassName;
    }

    /**
     * @param Channel $channel
     * @return MmbrExtdMergeVarExportIterator
     * @throws \InvalidArgumentException if MemberExtendedMergeVar class name is not provided
     */
    protected function getExtendedMergeVarIterator(Channel $channel)
    {
        if (!is_string($this->mmbrExtdMergeVarClassName) && empty($this->mmbrExtdMergeVarClassName)) {
            throw new \InvalidArgumentException('MemberExtendedMergeVar class name must be provided.');
        }

        $iterator = new MmbrExtdMergeVarExportIterator(
            $this->getSegmentsIterator($channel),
            $this->doctrineHelper,
            $this->mmbrExtdMergeVarClassName
        );
        return $iterator;
    }
}
