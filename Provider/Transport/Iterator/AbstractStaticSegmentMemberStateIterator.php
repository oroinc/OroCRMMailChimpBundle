<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

abstract class AbstractStaticSegmentMemberStateIterator extends AbstractStaticSegmentIterator
{
    /**
     * @var string
     */
    protected $segmentMemberClassName;

    /**
     * @param string $segmentMemberClassName
     */
    public function setSegmentMemberClassName($segmentMemberClassName)
    {
        $this->segmentMemberClassName = $segmentMemberClassName;
    }
}
