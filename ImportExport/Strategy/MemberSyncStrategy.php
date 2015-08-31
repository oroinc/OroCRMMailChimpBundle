<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy as BaseStrategy;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarProviderInterface;

class MemberSyncStrategy extends BaseStrategy
{
    /**
     * @var MergeVarProviderInterface
     */
    protected $mergeVarProvider;

    /**
     * @var array
     */
    protected $processedMembers = [];

    /**
     * @param Member $entity
     * @return Member|null
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        if ($this->isEntityProcessed($entity)) {
            return null;
        }

        /** @var Member $entity */
        $entity = $this->beforeProcessEntity($entity);
        $entity = $this->processEntity($entity);
        $entity = $this->afterProcessEntity($entity);
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }

    /**
     * @param Member $member
     * @return Member
     */
    protected function processEntity(Member $member)
    {
        $member->setSubscribersList(
            $this->databaseHelper->getEntityReference($member->getSubscribersList())
        );
        $member->setChannel(
            $this->databaseHelper->getEntityReference($member->getChannel())
        );

        return $member;
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
     */
    protected function collectEntities($entity)
    {
        $this->processedMembers[$entity->getSubscribersList()->getId()][$entity->getEmail()] = true;
    }

    /**
     * @param Member $entity
     *
     * @return bool
     */
    protected function isEntityProcessed($entity)
    {
        return !empty($this->processedMembers[$entity->getSubscribersList()->getId()][$entity->getEmail()]);
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
     * @param Member $entity
     * @return null|Member
     */
    protected function validateAndUpdateContext(Member $entity)
    {
        // validate entity
        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->context->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->context);
            return null;
        }

        // increment context counter
        $this->context->incrementAddCount();

        return $entity;
    }
}
