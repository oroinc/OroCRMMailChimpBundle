<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\ImportExportBundle\Field\FieldHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy as BasicImportStrategy;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;

class MemberActivityImportStrategy extends BasicImportStrategy implements LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DefaultOwnerHelper
     */
    protected $ownerHelper;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ImportStrategyHelper $strategyHelper,
        FieldHelper $fieldHelper,
        DatabaseHelper $databaseHelper,
        DoctrineHelper $doctrineHelper
    ) {
        parent::__construct($eventDispatcher, $strategyHelper, $fieldHelper, $databaseHelper);

        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param DefaultOwnerHelper $ownerHelper
     */
    public function setOwnerHelper(DefaultOwnerHelper $ownerHelper)
    {
        $this->ownerHelper = $ownerHelper;
    }

    /**
     * @param MemberActivity $entity
     * @return MemberActivity
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        $entity = $this->beforeProcessEntity($entity);
        $entity = $this->processEntity($entity);
        $entity = $this->afterProcessEntity($entity);

        return $entity;
    }

    /**
     * Member Activity is added only for known member.
     * There can not be two send activities in campaign for same member.
     *
     * @param MemberActivity $entity
     * @return MemberActivity
     */
    protected function processEntity($entity)
    {
        if ($this->logger) {
            $this->logger->info(
                sprintf(
                    'Processing MailChimp Member Activity [email=%s, action=%s]',
                    $entity->getEmail(),
                    $entity->getAction()
                )
            );
        }

        if ($this->isSkipped($entity)) {
            return null;
        }

        $channel = $this->getEntityReference($entity->getChannel());
        /** @var Campaign $campaign */
        $campaign = $this->findExistingEntity($entity->getCampaign());
        $member = $this->findExistingMember($entity->getMember(), $channel, $campaign);

        if ($member) {
            $entity->setChannel($channel);
            $entity->setCampaign($campaign);
            $entity->setMember($member);
            if (!$entity->getActivityTime()) {
                $entity->setActivityTime($campaign->getSendTime());
            }
            $this->context->incrementAddCount();

            return $entity;
        }

        return null;
    }

    /**
     * @param Member $member
     * @param Channel $channel
     * @param Campaign $campaign
     * @return Member
     */
    protected function findExistingMember(Member $member, Channel $channel, Campaign $campaign)
    {
        $searchCondition = [
            'channel' => $channel,
            'subscribersList' => $campaign->getSubscribersList(),
            'email' => $member->getEmail()
        ];

        return $this->findEntityByIdentityValues(ClassUtils::getClass($member), $searchCondition);
    }

    /**
     * @param object $entity
     * @return object
     */
    protected function getEntityReference($entity)
    {
        return $this->doctrineHelper->getEntityReference(ClassUtils::getClass($entity), $entity->getId());
    }

    /**
     * @param MemberActivity $entity
     * @return bool
     */
    protected function isSkipped(MemberActivity $entity)
    {
        if ($entity->getAction() === 'send') {
            $searchCondition = [
                'campaign' => $entity->getCampaign(),
                'action' => $entity->getAction()
            ];

            return (bool)$this->findEntityByIdentityValues(ClassUtils::getClass($entity), $searchCondition);
        }

        return false;
    }
}
