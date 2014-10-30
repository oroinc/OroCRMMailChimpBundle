<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

interface SubordinateReaderInterface
{
    /**
     * @return bool
     */
    public function writeRequired();
}
