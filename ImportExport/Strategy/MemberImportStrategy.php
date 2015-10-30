<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarProviderInterface;

class MemberImportStrategy extends AbstractImportStrategy
{
    /**
     * @var array
     */
    protected static $processedEmails = [];

    /**
     * @var MergeVarProviderInterface
     */
    protected $mergeVarProvider;

    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * @var EntityManager
     */
    protected $memberEntityManager;

    /**
     * @var array
     */
    protected $memberIdentityFields;

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

        // Replace subscribers list if required
        /** @var SubscribersList $subscribersList */
        if (!$existingEntity->getSubscribersList()) {
            $itemData = $this->context->getValue('itemData');
            $subscribersList = $this->updateRelatedEntity(
                $existingEntity->getSubscribersList(),
                $entity->getSubscribersList(),
                $itemData['subscribersList']
            );
            $existingEntity->setSubscribersList($subscribersList);
        }

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
        self::$processedEmails[$entity->getSubscribersList()->getId()][$entity->getEmail()] = true;
    }

    /**
     * @param Member $entity
     *
     * @return bool
     */
    protected function isEntityProcessed($entity)
    {
        return !empty(self::$processedEmails[$entity->getSubscribersList()->getId()][$entity->getEmail()]);
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
        $entityName = $this->getMemberClassName($member);
        $em = $this->getMemberEntityManager($entityName);
        $identityValues = $this->getMemberIdentityValues($member);
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

    /**
     * @param Member $member
     * @return string
     */
    protected function getMemberClassName(Member $member)
    {
        if (!$this->memberClassName) {
            $this->memberClassName = ClassUtils::getClass($member);
        }

        return $this->memberClassName;
    }

    /**
     * @param string $entityName
     * @return EntityManager
     */
    protected function getMemberEntityManager($entityName)
    {
        if (!$this->memberEntityManager) {
            $this->memberEntityManager = $this->strategyHelper->getEntityManager($entityName);
        }

        return $this->memberEntityManager;
    }

    /**
     * @param string $entityName
     * @return array
     */
    protected function getMemberIdentityFields($entityName)
    {
        if (!$this->memberIdentityFields) {
            $fields = $this->fieldHelper->getFields($entityName, true);

            foreach ($fields as $field) {
                $fieldName = $field['name'];
                if (!$this->fieldHelper->getConfigValue($entityName, $fieldName, 'excluded', false)
                    && $this->fieldHelper->getConfigValue($entityName, $fieldName, 'identity', false)
                ) {
                    $this->memberIdentityFields[] = $fieldName;
                }
            }
        }

        return $this->memberIdentityFields;
    }

    /**
     * @param Member $member
     * @return array
     */
    protected function getMemberIdentityValues(Member $member)
    {
        $identityFields = $this->getMemberIdentityFields($this->getMemberClassName($member));

        $identityValues = [];
        foreach ($identityFields as $fieldName) {
            $identityValues[$fieldName] = $this->fieldHelper->getObjectValue($member, $fieldName);
        }

        return $identityValues;
    }
}
