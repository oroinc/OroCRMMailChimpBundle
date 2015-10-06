<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarProviderInterface;

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
        /** @var Member $existingEntity */
        $existingEntity = $this->findExistingMember($entity);
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
        if ($entity) {
            $entity = $this->validateAndUpdateContext($entity);
        }

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
        /** @var SubscribersList $subscribersList */
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
     * @param Member $entity
     * @return Member|null
     */
    protected function afterProcessEntity($entity)
    {
        if ($this->isEntityProcessed($entity)) {
            return null;
        }

        $this->assignMergeVarValues($entity);

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Member $entity
     */
    protected function collectEntities($entity)
    {
        $jobContext = $this->getJobContext();
        $processedMembers = (array)$jobContext->get('processed_members');
        $processedMembers[$entity->getSubscribersList()->getId()][$entity->getEmail()] = true;
        $jobContext->put('processed_members', $processedMembers);
    }

    /**
     * @param Member $entity
     *
     * @return bool
     */
    protected function isEntityProcessed($entity)
    {
        $jobContext = $this->getJobContext();
        $processedMembers = (array)$jobContext->get('processed_members');

        return !empty($processedMembers[$entity->getSubscribersList()->getId()][$entity->getEmail()]);
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

    /**
     * @param Member $member
     * @return null|Member
     */
    public function findExistingMember(Member $member)
    {
        $entityName = ClassUtils::getClass($member);
        $em = $this->strategyHelper->getEntityManager($entityName);
        $identityValues = $this->fieldHelper->getIdentityValues($member);
        $fields = [
            'id',
            'originId',
            'subscribersList',
            'email',
            'mergeVarValues',
            'firstName',
            'lastName',
            'phone'
        ];

        $queryBuilder = $em->createQueryBuilder()->from($entityName, 'e');
        $queryBuilder->select(sprintf('partial e.{%s}', implode(',', $fields)));

        $where = $queryBuilder->expr()->andX();
        foreach ($identityValues as $field => $value) {
            $where->add(sprintf('e.%s = :%s', $field, $field));
        }

        $queryBuilder->where($where)
            ->setParameters($identityValues)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
