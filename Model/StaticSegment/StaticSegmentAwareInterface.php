<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\StaticSegment;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;

interface StaticSegmentAwareInterface
{
    const OPTION_SEGMENT = 'staticSegment';

    /**
     * @return StaticSegment
     */
    public function getStaticSegment();
}
