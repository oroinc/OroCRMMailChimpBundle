<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Strategy;

use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;
use Oro\Bundle\MailChimpBundle\Model\MergeVar\MergeVarProviderInterface;

class MemberImportStrategy extends AbstractImportStrategy
{
    /**
     * @var MergeVarProviderInterface
     */
    protected $mergeVarProvider;

    /**
     * @param Member $entity
     * @return Member|null
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        /** @var Member $entity */
        $entity = $this->beforeProcessEntity($entity);
        $subscribersList = $this->getSubscribersList($entity);
        if (!$subscribersList) {
            return null;
        }
        $entity->setSubscribersList($subscribersList);

        /** @var Member $existingEntity */
        $existingEntity = $this->findExistingEntity($entity);
        if ($existingEntity) {
            if ($this->logger) {
                $this->logger->notice('Syncing Existing MailChimp Member [origin_id=' . $entity->getOriginId() . ']');
            }

            $entity = $this->importExistingMember($entity, $existingEntity);
        } else {
            if ($this->logger) {
                $this->logger->notice('Adding new MailChimp Member [origin_id=' . $entity->getOriginId() . ']');
            }

            $entity = $this->processEntity($entity, true, true, $this->context->getValue('itemData'));
        }

        $entity = $this->afterProcessEntity($entity);
        if ($entity) {
            $entity = $this->validateAndUpdateContext($entity);
        }

        return $entity;
    }

    /**
     * @param Member $member
     * @return null|SubscribersList
     */
    protected function getSubscribersList(Member $member)
    {
        $subscribersList = $member->getSubscribersList();
        if (!$subscribersList) {
            return null;
        }
        if ($subscribersList->getId()) {
            $subscribersList = $this->databaseHelper->getEntityReference($subscribersList);
        } else {
            $subscribersList = $this->findExistingEntity($subscribersList);
        }

        if (!$subscribersList) {
            return null;
        }

        return $subscribersList;
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
        $existingEntity->setOriginId($entity->getOriginId());
        $existingEntity->setStatus($entity->getStatus());
        $existingEntity->setMemberRating($entity->getMemberRating());
        $existingEntity->setOptedInAt($entity->getOptedInAt());
        $existingEntity->setOptedInIpAddress($entity->getOptedInIpAddress());
        $existingEntity->setConfirmedAt($entity->getConfirmedAt());
        $existingEntity->setConfirmedIpAddress($entity->getConfirmedIpAddress());
        $existingEntity->setLatitude($entity->getLatitude());
        $existingEntity->setLongitude($entity->getLongitude());
        $existingEntity->setDstOffset($entity->getDstOffset());
        $existingEntity->setGmtOffset($entity->getGmtOffset());
        $existingEntity->setTimezone($entity->getTimezone());
        $existingEntity->setCc($entity->getCc());
        $existingEntity->setRegion($entity->getRegion());
        $existingEntity->setLastChangedAt($entity->getLastChangedAt());
        $existingEntity->setEuid($entity->getEuid());
        $existingEntity->setMergeVarValues($entity->getMergeVarValues());

        return $existingEntity;
    }

    /**
     * Set EmailCampaign owner.
     *
     * @param Member $entity
     * @return Member|null
     */
    protected function afterProcessEntity($entity)
    {
        $this->assignMergeVarValues($entity);

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Member $entity
     * @return bool
     */
    protected function collectEntities($entity)
    {
        return false;
    }

    /**
     * Assign MergeVar values to properties of Member
     *
     * @param Member $member
     */
    protected function assignMergeVarValues(Member $member)
    {
        $subscribersList = $member->getSubscribersList();
        if (!$subscribersList) {
            return;
        }

        $this->mergeVarProvider->assignMergeVarValues(
            $member,
            $this->mergeVarProvider->getMergeVarFields($subscribersList)
        );
    }

    /**
     * @param MergeVarProviderInterface $mergeVarProvider
     */
    public function setMergeVarProvider(MergeVarProviderInterface $mergeVarProvider)
    {
        $this->mergeVarProvider = $mergeVarProvider;
    }
}
