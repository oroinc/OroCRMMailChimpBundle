<?php
namespace Oro\Bundle\MailChimpBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use Oro\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentsMemberStateManager;
use Oro\Bundle\MailChimpBundle\Provider\Connector\MemberConnector;
use Oro\Bundle\MailChimpBundle\Provider\Connector\StaticSegmentConnector;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;

class ExportMailChimpProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var ReverseSyncProcessor
     */
    private $reverseSyncProcessor;

    /**
     * @var StaticSegmentsMemberStateManager
     */
    private $staticSegmentsMemberStateManager;

    /**
     * @var JobRunner
     */
    private $jobRunner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ReverseSyncProcessor $reverseSyncProcessor
     * @param StaticSegmentsMemberStateManager $staticSegmentsMemberStateManager
     * @param JobRunner $jobRunner
     * @param LoggerInterface $logger
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ReverseSyncProcessor $reverseSyncProcessor,
        StaticSegmentsMemberStateManager $staticSegmentsMemberStateManager,
        JobRunner $jobRunner,
        LoggerInterface $logger
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->reverseSyncProcessor = $reverseSyncProcessor;
        $this->staticSegmentsMemberStateManager = $staticSegmentsMemberStateManager;
        $this->jobRunner = $jobRunner;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());
        $body = array_replace_recursive([
            'integrationId' => null,
            'segmentsIds' => [],
        ], $body);

        if (! $body['integrationId']) {
            $this->logger->critical('The message invalid. It must have integrationId set', ['message' => $message]);

            return self::REJECT;
        }

        if (! $body['segmentsIds']) {
            $this->logger->critical('The message invalid. It must have segmentsIds set', ['message' => $message]);

            return self::REJECT;
        }

        /** @var EntityManagerInterface $em */
        $em = $this->doctrineHelper->getEntityManagerForClass(Integration::class);

        /** @var Integration $integration */
        $integration = $em->find(Integration::class, $body['integrationId']);

        if (! $integration) {
            $this->logger->error(
                sprintf('The integration not found: %s', $body['integrationId']),
                ['message' => $message]
            );

            return self::REJECT;
        }
        if (! $integration->isEnabled()) {
            $this->logger->error(
                sprintf('The integration is not enabled: %s', $body['integrationId']),
                ['message' => $message]
            );

            return self::REJECT;
        }

        $jobName = 'oro_mailchimp:export_mail_chimp:'.$body['integrationId'];
        $ownerId = $message->getMessageId();

        $result = $this->jobRunner->runUnique($ownerId, $jobName, function () use ($body, $integration) {
            return $this->processMessageData($body, $integration);
        });

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * @param array $body
     * @return bool
     */
    protected function processMessageData(array $body, $integration)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->doctrineHelper->getEntityManagerForClass(Integration::class);

        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $segmentsIds = $body['segmentsIds'];
        /** @var StaticSegmentRepository $staticSegmentRepository */
        $staticSegmentRepository = $this->doctrineHelper->getEntityRepository(StaticSegment::class);

        $segmentsIdsToSync = [];
        $syncStatuses = [StaticSegment::STATUS_NOT_SYNCED, StaticSegment::STATUS_SCHEDULED];
        foreach ($segmentsIds as $segmentId) {
            /** @var StaticSegment $staticSegment */
            $staticSegment = $staticSegmentRepository->find($segmentId);
            if ($staticSegment && in_array($staticSegment->getSyncStatus(), $syncStatuses, true)) {
                $this->setStaticSegmentStatus($staticSegment, StaticSegment::STATUS_IN_PROGRESS);
                $segmentsIdsToSync[] = $segmentId;
            }
        }

        $parameters = ['segments' => $segmentsIdsToSync];
        $this->reverseSyncProcessor->process($integration, MemberConnector::TYPE, $parameters);
        $this->reverseSyncProcessor->process($integration, StaticSegmentConnector::TYPE, $parameters);

        // reverse sync process does implicit entity manager clear, we have to re-query everything again.
        foreach ($segmentsIdsToSync as $segmentId) {
            /** @var StaticSegment $staticSegment */
            $staticSegment = $staticSegmentRepository->find($segmentId);
            if ($staticSegment) {
                $this->setStaticSegmentStatus($staticSegment, StaticSegment::STATUS_SYNCED);
            }
        }

        return true;
    }

    /**
     * @param StaticSegment $staticSegment
     * @param string $status
     * @param bool $lastSynced
     */
    protected function setStaticSegmentStatus(StaticSegment $staticSegment, $status, $lastSynced = false)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->doctrineHelper->getEntityManager($staticSegment);

        $staticSegment->setSyncStatus($status);

        if ($lastSynced) {
            $staticSegment->setLastSynced(new \DateTime('now', new \DateTimeZone('UTC')));
        }

        $em->persist($staticSegment);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::EXPORT_MAILCHIMP_SEGMENTS];
    }
}
