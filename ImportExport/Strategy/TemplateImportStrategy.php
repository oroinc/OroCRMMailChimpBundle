<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use OroCRM\Bundle\MailChimpBundle\Entity\Template;

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
