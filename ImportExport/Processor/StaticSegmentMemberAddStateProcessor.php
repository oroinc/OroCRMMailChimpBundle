<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Processor;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentAwareInterface;

class StaticSegmentMemberAddStateProcessor extends ImportProcessor implements StaticSegmentAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function getStaticSegment()
    {
        return $this->context->getOption(StaticSegmentAwareInterface::OPTION_SEGMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $staticSegmentMember = new StaticSegmentMember();
        $staticSegmentMember
            ->setMember($item)
            ->setStaticSegment($this->getStaticSegment());

        if ($this->strategy) {
            $staticSegmentMember = $this->strategy->process($staticSegmentMember);
        }

        return $staticSegmentMember;
    }
}
