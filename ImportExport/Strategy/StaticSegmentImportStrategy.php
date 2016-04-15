<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;

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
        array $searchContext = [],
        $entityIsRelation = false
    ) {
        return parent::processEntity($entity, $isFullData, false, $itemData, $searchContext, $entityIsRelation);
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
     * Sync only existing StaticSegments, do not create them from MailChimp
     *
     * @param StaticSegment $entity
     *
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        if (!$entity) {
            return null;
        }

        if (!$entity->getSyncStatus()) {
            $entity->setSyncStatus(StaticSegment::STATUS_IMPORTED);
        }

        return parent::afterProcessEntity($entity);
    }

    /**
     * Sync only existing StaticSegments, do not create them from MailChimp
     *
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
