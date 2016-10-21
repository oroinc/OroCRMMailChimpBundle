<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Reader;

interface SubordinateReaderInterface
{
    /**
     * Determines that subordinate iterator was changed
     *
     * @return bool
     */
    public function writeRequired();
}
