<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MailChimpBundle\Entity\Template;

class CampaignImportStrategy extends AbstractImportStrategy
{
    /**
     * @param Campaign $entity
     * @return Campaign|null
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        $this->cachedEntities = [];
        $entity = $this->beforeProcessEntity($entity);

        $existingEntity = $this->findExistingEntity($entity);
        if ($existingEntity) {
            if ($this->logger) {
                $this->logger->info('Syncing Existing MailChimp Campaign [origin_id=' . $entity->getOriginId() . ']');
            }

            $entity = $this->importExistingCampaign($entity, $existingEntity);
        } else {
            if ($this->logger) {
                $this->logger->info('Adding new MailChimp Campaign [origin_id=' . $entity->getOriginId() . ']');
            }

            $entity = $this->processEntity($entity, true, true, $this->context->getValue('itemData'));
        }

        $entity = $this->afterProcessEntity($entity);
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }

    /**
     * Update existing MailChimp Email Campaign.
     *
     * @param Campaign $entity
     * @param Campaign $existingEntity
     * @return Campaign
     */
    protected function importExistingCampaign(Campaign $entity, Campaign $existingEntity)
    {
        $itemData = $this->context->getValue('itemData');

        // Update MailChimp campaign
        $this->importExistingEntity(
            $entity,
            $existingEntity,
            $itemData,
            ['channel', 'template', 'subscribersList', 'staticSegment', 'emailCampaign']
        );

        // Replace Template if required
        /** @var Template $template */
        $template = $this->updateRelatedEntity(
            $existingEntity->getTemplate(),
            $entity->getTemplate(),
            $itemData['template']
        );
        $existingEntity->setTemplate($template);

        // Replace subscribers list if required
        /** @var SubscribersList $subscribersList */
        $subscribersList = $this->updateRelatedEntity(
            $existingEntity->getSubscribersList(),
            $entity->getSubscribersList(),
            $itemData['subscribersList']
        );
        $existingEntity->setSubscribersList($subscribersList);

        // Replace StaticSegment if required
        /** @var StaticSegment $staticSegment */
        $staticSegment = $this->updateRelatedEntity(
            $existingEntity->getStaticSegment(),
            $entity->getStaticSegment(),
            $itemData['staticSegment']
        );
        $existingEntity->setStaticSegment($staticSegment);

        return $existingEntity;
    }
}
