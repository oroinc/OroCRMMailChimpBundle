<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;

use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

class ListImportStrategy extends AbstractImportStrategy
{
    /**
     * @param SubscribersList $entity
     * @return SubscribersList|null
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
                $this->logger->info('Syncing Existing MailChimp List [origin_id=' . $entity->getOriginId() . ']');
            }

            $entity = $this->importExistingList($entity, $existingEntity);
        } else {
            if ($this->logger) {
                $this->logger->info('Adding new MailChimp List [origin_id=' . $entity->getOriginId() . ']');
            }

            $entity = $this->processEntity($entity, true, true, $this->context->getValue('itemData'));
        }

        $entity = $this->afterProcessEntity($entity);
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }

    /**
     * Update existing MailChimp Email List.
     *
     * @param SubscribersList $entity
     * @param SubscribersList $existingEntity
     * @return SubscribersList
     */
    protected function importExistingList(SubscribersList $entity, SubscribersList $existingEntity)
    {
        $itemData = $this->context->getValue('itemData');

        // Update MailChimp List
        $this->importExistingEntity(
            $entity,
            $existingEntity,
            $itemData,
            ['channel', 'marketingList']
        );

        // Update related MarketingList
        $existingMarketingList = $existingEntity->getMarketingList();
        if ($existingMarketingList) {
            $this->importExistingEntity(
                $entity->getMarketingList(),
                $existingMarketingList,
                $itemData['emailCampaign']
            );
        } else {
            $existingEntity->setMarketingList($entity->getMarketingList());
        }

        return $existingEntity;
    }

    /**
     * Set MarketingList owner.
     *
     * @param SubscribersList $entity
     * @return SubscribersList
     */
    protected function afterProcessEntity($entity)
    {
        $this->ownerHelper->populateChannelOwner($entity->getMarketingList(), $entity->getChannel());

        return parent::afterProcessEntity($entity);
    }
}
