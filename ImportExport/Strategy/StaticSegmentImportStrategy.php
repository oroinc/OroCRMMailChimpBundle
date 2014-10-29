<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

class StaticSegmentImportStrategy extends AbstractImportStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
    {
        if ($this->logger) {
            $this->logger->info('Syncing MailChimp Static Segment [origin_id=' . $entity->getOriginId() . ']');
        }

        return parent::beforeProcessEntity($entity);
    }
}
