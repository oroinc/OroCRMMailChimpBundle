<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Processor;

use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter\MemberSyncDataConverter;

class MembersSyncProcessor extends ImportProcessor
{
    /**
     * @var MemberSyncDataConverter
     */
    protected $dataConverter;

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if ($this->dataConverter && $item instanceof FullNameInterface) {
            $item = $this->dataConverter->convertObjectToImportFormat($item);
        }

        return parent::process($item);
    }
}
