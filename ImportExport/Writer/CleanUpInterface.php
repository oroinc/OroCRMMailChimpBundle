<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

interface CleanUpInterface
{
    /**
     * Remove outdated records.
     *
     * @param array $item
     */
    public function cleanUp(array $item);
}
