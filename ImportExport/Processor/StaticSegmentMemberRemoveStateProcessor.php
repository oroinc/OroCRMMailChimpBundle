<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Processor;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentMemberRemoveStateProcessor extends ImportProcessor
{
    /**
     * @param StaticSegmentMember $item
     *
     * {@inheritdoc}
     */
    public function process($item)
    {
        $item->setState(StaticSegmentMember::STATE_REMOVE);

        if ($this->strategy) {
            $item = $this->strategy->process($item);
        }

        return $item;
    }
}
