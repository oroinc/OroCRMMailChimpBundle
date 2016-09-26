<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Strategy;

use Oro\Bundle\MailChimpBundle\Entity\Template;

class TemplateImportStrategy extends AbstractImportStrategy
{
    /**
     * @param Template $entity
     * @return Template
     */
    protected function beforeProcessEntity($entity)
    {
        if ($this->logger) {
            $this->logger->info('Syncing MailChimp Template [origin_id=' . $entity->getOriginId() . ']');
        }

        return parent::beforeProcessEntity($entity);
    }
}
