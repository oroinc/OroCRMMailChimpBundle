<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;

class MemberImportStrategy extends AbstractImportStrategy
{
    /**
     * @param Member $entity
     * @return Member|null
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        $entity = $this->beforeProcessEntity($entity);

        $entityName = ClassUtils::getClass($entity);
        $fields = $this->fieldHelper->getFields($entityName, true);

        $existingEntity = $this->findExistingEntity($entity, $fields);
        if ($existingEntity) {
            if ($this->logger) {
                $this->logger->info('Syncing Existing MailChimp Member [origin_id=' . $entity->getOriginId() . ']');
            }

            $entity = $this->importExistingMember($entity, $existingEntity);
        } else {
            if ($this->logger) {
                $this->logger->info('Adding new MailChimp Member [origin_id=' . $entity->getOriginId() . ']');
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
     * @param Member $entity
     * @param Member $existingEntity
     * @return Member
     */
    protected function importExistingMember(Member $entity, Member $existingEntity)
    {
        $itemData = $this->context->getValue('itemData');

        // Update MailChimp List
        $this->importExistingEntity(
            $entity,
            $existingEntity,
            $itemData,
            ['channel', 'subscribersList']
        );

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
}