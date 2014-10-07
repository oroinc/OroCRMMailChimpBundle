<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;

class CampaignImportStrategy extends AbstractImportStrategy
{
    /**
     * @var DefaultOwnerHelper
     */
    protected $ownerHelper;

    /**
     * @param DefaultOwnerHelper $ownerHelper
     */
    public function setOwnerHelper(DefaultOwnerHelper $ownerHelper)
    {
        $this->ownerHelper = $ownerHelper;
    }

    /**
     * @param Campaign $entity
     * @return Campaign|null
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        $this->cachedEntities = array();
        $entity = $this->beforeProcessEntity($entity);

        $entityName = ClassUtils::getClass($entity);
        $fields = $this->fieldHelper->getFields($entityName, true);

        $existingEntity = $this->findExistingEntity($entity, $fields);
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
                $itemData['emailCampaign']
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
     * Update related entity.
     *
     * @param object|null $entity
     * @param object $importedEntity
     * @param array|null $data
     * @return null|object
     */
    protected function updateRelatedEntity($entity, $importedEntity, $data)
    {
        if (!$entity) {
            $entity = $importedEntity;
        }

        return $this->processEntity($entity, false, false, $data);
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

        return parent::afterProcessEntity($entity);
    }
}
