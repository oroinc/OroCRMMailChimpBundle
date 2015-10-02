<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Doctrine\Common\Util\ClassUtils;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\Validator\ValidatorInterface;

use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy as BasicImportStrategy;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;

class MemberActivityImportStrategy extends BasicImportStrategy implements
    LoggerAwareInterface,
    StepExecutionAwareInterface
{
    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var array
     */
    protected $singleInstanceActivities = [
        MemberActivity::ACTIVITY_SENT,
        MemberActivity::ACTIVITY_UNSUB,
        MemberActivity::ACTIVITY_BOUNCE
    ];

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ValidatorInterface $validator
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
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

        /** @var Channel $channel */
        $channel = $this->databaseHelper->getEntityReference($entity->getChannel());
        /** @var Campaign $campaign */
        $campaign = $this->databaseHelper->getEntityReference($entity->getCampaign());
        $member = $this->findExistingMember($entity->getMember(), $channel, $campaign);

        $entity
            ->setChannel($channel)
            ->setCampaign($campaign)
            ->setMember($member);

        if ($member && !$this->isSkipped($entity)) {
            if (!$entity->getActivityTime()) {
                $entity->setActivityTime($campaign->getSendTime());
            }
            $this->context->incrementAddCount();

            if ($this->logger) {
                $this->logger->info(
                    sprintf(
                        '    Activity added for MailChimp Member [id=%d]',
                        $member->getId()
                    )
                );
            }

            return $entity;
        } elseif ($this->logger) {
            $this->logger->info('    Activity skipped');
        }

        return null;
    }

    /**
     * @param MemberActivity $entity
     * @return MemberActivity
     */
    protected function afterProcessEntity($entity)
    {
        if (!$entity) {
            return null;
        }

        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->context->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->context);

            return null;
        }

        $jobContext = $this->getJobContext();
        $processedCampaigns = (array)$jobContext->get('processed_campaigns');
        $campaignId = $entity->getCampaign()->getId();
        if (!in_array($campaignId, $processedCampaigns)) {
            $processedCampaigns[] = $campaignId;
        }
        $jobContext->put('processed_campaigns', $processedCampaigns);

        return parent::afterProcessEntity($entity);
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
        ];

        if ($originId = $member->getOriginId()) {
            $searchCondition['originId'] = $originId;
        } else {
            $searchCondition['email'] = $member->getEmail();
        }

        return $this->findEntityByIdentityValues(ClassUtils::getClass($member), $searchCondition);
    }

    /**
     * @param MemberActivity $entity
     * @return bool
     */
    protected function isSkipped(MemberActivity $entity)
    {
        if (in_array($entity->getAction(), $this->singleInstanceActivities)) {
            $searchCondition = [
                'campaign' => $entity->getCampaign(),
                'action' => $entity->getAction(),
                'member' => $entity->getMember()
            ];

            return (bool)$this->findEntityByIdentityValues(ClassUtils::getClass($entity), $searchCondition);
        }

        return false;
    }

    /**
     * @return ExecutionContext
     */
    protected function getJobContext()
    {
        /** @var JobExecution $jobExecution */
        $jobExecution = $this->stepExecution->getJobExecution();

        return $jobExecution->getExecutionContext();
    }
}
