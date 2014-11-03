<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

class StaticSegmentImportStrategy extends AbstractImportStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function processEntity(
        $entity,
        $isFullData = false,
        $isPersistNew = false,
        $itemData = null,
        array $searchContext = []
    ) {
        return parent::processEntity($entity, $isFullData, false, $itemData, $searchContext);
    }

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

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateAndUpdateContext($entity)
    {
        if (!$entity) {
            return null;
        }

        return parent::validateAndUpdateContext($entity);
    }
}
