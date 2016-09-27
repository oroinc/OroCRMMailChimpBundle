<?php

namespace Oro\Bundle\MailChimpBundle\Entity;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

interface OriginAwareInterface
{
    /**
     * Get origin ID.
     *
     * @return mixed
     */
    public function getOriginId();

    /**
     * Get integration channel.
     *
     * @return Channel
     */
    public function getChannel();
}
