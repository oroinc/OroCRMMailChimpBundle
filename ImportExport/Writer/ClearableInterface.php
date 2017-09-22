<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

/**
 * This interface allows to clear state of writer in case of StepExecutor
 */
interface ClearableInterface
{
    /**
     * Clear state of writer
     *
     * @return null
     */
    public function clear();
}
