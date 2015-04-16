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
        if (!is_string($mmbrExtdMergeVarClassName) && empty($mmbrExtdMergeVarClassName)) {
            throw new \InvalidArgumentException('MemberExtendedMergeVar class name must be provided');
        }
        $this->mmbrExtdMergeVarClassName = $mmbrExtdMergeVarClassName;
    }

    /**
     * @param Channel $channel
     * @return \Iterator
     */
    protected function getExtendedMergeVarIterator(Channel $channel)
    {
        $iterator = new MmbrExtdMergeVarExportIterator(
            $this->getSegmentsIterator($channel),
            $this->doctrineHelper,
            $this->mmbrExtdMergeVarClassName
        );
        return $iterator;
    }
}
