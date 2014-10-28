<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Processor;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentAwareInterface;

class StaticSegmentMemberAddStateProcessor extends ImportProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $staticSegmentMember = new StaticSegmentMember();
        $staticSegmentMember
            ->setMember($item)
            ->setStaticSegment($this->context->getOption(StaticSegmentAwareInterface::OPTION_SEGMENT));

        return $staticSegmentMember;
    }
}
