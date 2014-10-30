<?php

namespace OroCRM\Bundle\MailChimpBundle\Placeholder;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class PlaceholderFilter
{
    /**
     * Checks the object is an instance of a given class.
     *
     * @return bool
     */
    public function isApplicable($entity)
    {
        return $entity instanceof MarketingList;
    }
}
