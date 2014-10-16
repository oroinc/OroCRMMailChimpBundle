<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransportSettings;

class CampaignImportStrategy extends AbstractImportStrategy
{
    /**
     * @param Campaign $entity
     * @return Campaign|null
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        $this->cachedEntities = array();
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
            ['channel', 'template', 'subscribersList', 'emailCampaign']
        );

        // Update related Email Campaign
        $existingEmailCampaign = $existingEntity->getEmailCampaign();
        if ($existingEmailCampaign) {
            $this->importExistingEntity(
                $entity->getEmailCampaign(),
                $existingEmailCampaign,
                $itemData['emailCampaign'],
                ['transportSettings']
            );
        } else {
            $existingEntity->setEmailCampaign($entity->getEmailCampaign());
        }

        // Replace Template if required
        $template = $this->updateRelatedEntity(
            $existingEntity->getTemplate(),
            $entity->getTemplate(),
            $itemData['template']
        );
        $existingEntity->setTemplate($template);

        // Replace subscribers list if required
        $subscribersList = $this->updateRelatedEntity(
            $existingEntity->getSubscribersList(),
            $entity->getSubscribersList(),
            $itemData['subscribersList']
        );
        $existingEntity->setSubscribersList($subscribersList);

        return $existingEntity;
    }

    /**
     * Set EmailCampaign owner.
     *
     * @param Campaign $entity
     * @return Campaign
     */
    protected function afterProcessEntity($entity)
    {
        $this->ownerHelper->populateChannelOwner($entity->getEmailCampaign(), $entity->getChannel());

        // Update related Email Campaign transport settings
        $emailCampaign = $entity->getEmailCampaign();
        if ($emailCampaign) {
            $transportSettings = $emailCampaign->getTransportSettings();
            if (!$transportSettings) {
                $transportSettings = new MailChimpTransportSettings();
                $emailCampaign->setTransportSettings($transportSettings);
                $this->processEntity($transportSettings, false, true);
            }
            $transportSettings->setChannel($entity->getChannel());
            $transportSettings->setTemplate($entity->getTemplate());
        }

        return parent::afterProcessEntity($entity);
    }
}
