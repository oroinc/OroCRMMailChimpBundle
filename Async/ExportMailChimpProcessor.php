<?php
namespace OroCRM\Bundle\MailChimpBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentsMemberStateManager;
use OroCRM\Bundle\MailChimpBundle\Provider\Connector\MemberConnector;
use OroCRM\Bundle\MailChimpBundle\Provider\Connector\StaticSegmentConnector;

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
     * @param DoctrineHelper $doctrineHelper
     * @param ReverseSyncProcessor $reverseSyncProcessor
     * @param StaticSegmentsMemberStateManager $staticSegmentsMemberStateManager
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ReverseSyncProcessor $reverseSyncProcessor,
        StaticSegmentsMemberStateManager $staticSegmentsMemberStateManager
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->reverseSyncProcessor = $reverseSyncProcessor;
        $this->staticSegmentsMemberStateManager = $staticSegmentsMemberStateManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        // TODO CRM-5838 unique job

        $body = JSON::decode($message->getBody());
        $body = array_replace_recursive([
            'integrationId' => null,
            'segmentsIds' => [],
        ], $body);

        if (false == $body['integrationId']) {
            throw new \LogicException('The message invalid. It must have integrationId set');
        }
        if (false == $body['segmentsIds']) {
            throw new \LogicException('The message invalid. It must have segmentsIds set');
        }

        /** @var EntityManagerInterface $em */
        $em = $this->doctrineHelper->getEntityManagerForClass(Channel::class);

        /** @var Channel $channel */
        $channel = $em->find(Channel::class, $body['integrationId']);
        if (false == $channel) {
            return self::REJECT;
        }
        if (false == $channel->isEnabled()) {
            return self::REJECT;
        }

        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $segmentsIds = $body['segmentsIds'];
        /** @var StaticSegmentRepository $staticSegmentRepository */
        $staticSegmentRepository = $this->doctrineHelper->getEntityRepository(StaticSegment::class);

        $segmentsIdsToSync = [];
        $syncStatuses = [StaticSegment::STATUS_NOT_SYNCED, StaticSegment::STATUS_SCHEDULED];
        foreach ($segmentsIds as $segmentId) {
            /** @var StaticSegment $staticSegment */
            $staticSegment = $staticSegmentRepository->find($segmentId);
            if ($staticSegment && in_array($staticSegment->getSyncStatus(), $syncStatuses)) {
                $this->setStaticSegmentStatus($staticSegment, StaticSegment::STATUS_IN_PROGRESS);
                $segmentsIdsToSync[] = $segmentId;
            }
        }

        $parameters = ['segments' => $segmentsIdsToSync];
        $this->reverseSyncProcessor->process($channel, MemberConnector::TYPE, $parameters);
        $this->reverseSyncProcessor->process($channel, StaticSegmentConnector::TYPE, $parameters);

        // reverse sync process does implicit entity manager clear, we have to re-query everything again.
        foreach ($segmentsIdsToSync as $segmentId) {
            /** @var StaticSegment $staticSegment */
            $staticSegment = $staticSegmentRepository->find($segmentId);
            if ($staticSegment) {
                $this->setStaticSegmentStatus($staticSegment, StaticSegment::STATUS_SYNCED);
            }
        }

        return self::ACK;
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
        return [Topics::EXPORT_MAIL_CHIMP_SEGMENTS];
    }
}
