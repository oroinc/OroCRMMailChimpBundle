<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\AbstractQuery;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\Validator\ValidatorInterface;

use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy as BasicImportStrategy;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
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

        return $this->findEntity(ClassUtils::getClass($member), $searchCondition, ['id']);
    }

    /**
     * @param MemberActivity $entity
     * @return bool
     */
    protected function isSkipped(MemberActivity $entity)
    {
        if (in_array($entity->getAction(), $this->singleInstanceActivities, true)) {
            $searchCondition = [
                'campaign' => $entity->getCampaign(),
                'action' => $entity->getAction(),
                'member' => $entity->getMember()
            ];

            return (bool)$this->findEntity(
                ClassUtils::getClass($entity),
                $searchCondition,
                ['id'],
                AbstractQuery::HYDRATE_SCALAR
            );
        }

        return false;
    }

    /**
     * Try to find entity by identity fields if at least one is specified
     *
     * @param string $entityName
     * @param array $identityValues
     * @param array $partialFields
     * @param null|int $hydration
     * @return null|object
     */
    protected function findEntity($entityName, array $identityValues, array $partialFields, $hydration = null)
    {
        foreach ($identityValues as $value) {
            if (null !== $value && '' !== $value) {
                return $this->findOneBy($entityName, $identityValues, $partialFields, $hydration);
            }
        }

        return null;
    }

    /**
     * @param string $entityName
     * @param array $criteria
     * @param array|null $partialFields
     * @param int|null $hydration
     * @return null|object
     */
    public function findOneBy(
        $entityName,
        array $criteria,
        array $partialFields = null,
        $hydration = null
    ) {
        $em = $this->strategyHelper->getEntityManager($entityName);

        $queryBuilder = $em->createQueryBuilder()->from($entityName, 'e');
        if ($partialFields) {
            $queryBuilder->select(sprintf('partial e.{%s}', implode(',', $partialFields)));
        } else {
            $queryBuilder->select('e');
        }

        $where = $queryBuilder->expr()->andX();
        foreach ($criteria as $field => $value) {
            $where->add(sprintf('e.%s = :%s', $field, $field));
        }

        $queryBuilder->where($where)
            ->setParameters($criteria)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult($hydration);
    }
}
