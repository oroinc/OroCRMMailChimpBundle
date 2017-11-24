<?php

namespace Oro\Bundle\MailChimpBundle\Provider;

interface StaticSegmentSyncModeChoicesProviderInterface
{
    /**
     * @return array
     */
    public function getTranslatedChoices(): array;
}
