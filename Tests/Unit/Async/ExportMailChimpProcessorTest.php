<?php
namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Async;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Job\Context\SelectiveContextAggregator;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use Oro\Bundle\MailChimpBundle\Async\ExportMailChimpProcessor;
use Oro\Bundle\MailChimpBundle\Async\Topics;
use Oro\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentsMemberStateManager;
use Oro\Bundle\MailChimpBundle\Provider\Connector\MemberConnector;
use Oro\Bundle\MailChimpBundle\Provider\Connector\StaticSegmentConnector;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\Testing\ClassExtensionTrait;
use PHPUnit_Framework_MockObject_Matcher_Invocation;
use Psr\Log\LoggerInterface;

/**
 * @dbIsolationPerTest
 */
class ExportMailChimpProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        self::assertClassImplements(MessageProcessorInterface::class, ExportMailChimpProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        self::assertClassImplements(TopicSubscriberInterface::class, ExportMailChimpProcessor::class);
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        new ExportMailChimpProcessor(
            $this->createMock(DoctrineHelper::class),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $this->createLoggerMock()
        );
    }

    public function testShouldSubscribeOnExportMailChimpSegmentsTopic()
    {
        $this->assertEquals([Topics::EXPORT_MAILCHIMP_SEGMENTS], ExportMailChimpProcessor::getSubscribedTopics());
    }

    public function testShouldLogAndRejectIfMessageBodyMissIntegrationId()
    {
        $message = new NullMessage();
        $message->setBody('[]');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have integrationId set', ['message' => $message])
        ;

        $processor = new ExportMailChimpProcessor(
            $this->createMock(DoctrineHelper::class),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldLogAndRejectIfMessageBodyMissSegmentsIds()
    {
        $message = new NullMessage();
        $message->setBody('{"integrationId":1}');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have segmentsIds set', ['message' => $message])
        ;

        $processor = new ExportMailChimpProcessor(
            $this->createMock(DoctrineHelper::class),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }
    
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The malformed json given.
     */
    public function testThrowIfMessageBodyInvalidJson()
    {
        $processor = new ExportMailChimpProcessor(
            $this->createMock(DoctrineHelper::class),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $this->createLoggerMock()
        );

        $message = new NullMessage();
        $message->setBody('[}');

        $processor->process($message, new NullSession());
    }

    public function testShouldLogAndRejectIfIntegrationNotFound()
    {
        $message = new NullMessage();
        $message->setBody('{"integrationId":"theIntegrationId", "segmentsIds": 1}');
        $message->setMessageId('theMessageId');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('The integration not found: theIntegrationId', ['message' => $message])
        ;

        $processor = new ExportMailChimpProcessor(
            $this->createDoctrineHelperStub(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldLogAndRejectIfIntegrationNotEnabled()
    {
        $integration = new Integration();
        $integration->setEnabled(false);

        $doctrineHelper = $this->createDoctrineHelperStub($integration);

        $message = new NullMessage();
        $message->setBody('{"integrationId":"theIntegrationId", "segmentsIds": 1}');
        $message->setMessageId('theMessageId');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('The integration is not enabled: theIntegrationId', ['message' => $message])
        ;

        $processor = new ExportMailChimpProcessor(
            $doctrineHelper,
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    /**
     * @dataProvider processMessageDataProvider
     */
    public function testProcessMessageData(int $segmentId, array $segmentsIdsToSync, string $syncStatus)
    {
        $integrationId = 'theIntegrationId';
        $messageId = 'theMessageId';

        $jobRunner = $this->createJobRunnerMock();
        $jobRunner->expects($this->once())
                  ->method('runUnique')
                  ->with($messageId, 'oro_mailchimp:export_mailchimp:'.$integrationId, $this->isType('callable'))
                  ->willReturnCallback(function ($ownerId, $jobName, $callback) {
                      return $callback();
                  });

        $integration = new Integration();
        $integration->setEnabled(true);

        $staticSegment = $this->createMock(StaticSegment::class);
        $staticSegment
            ->expects($this->once())
            ->method('getSyncStatus')
            ->willReturn($syncStatus);

        $staticSegment
            ->expects($this->exactly(count($segmentsIdsToSync)*2))
            ->method('setSyncStatus')
            ->withConsecutive(['in_progress'], ['synced']);

        $staticSegment
            ->expects($this->exactly(count($segmentsIdsToSync)))
            ->method('setLastSynced')
            ->with($this->isInstanceOf(\DateTime::class));

        $staticSegmentRepository = $this->createMock(StaticSegmentRepository::class);
        $staticSegmentRepository
            ->expects($this->exactly(1+count($segmentsIdsToSync)))
            ->method('find')
            ->with($segmentId)
            ->willReturn($staticSegment);

        $segmentEntityManager = $this
            ->getStaticSegmentEntityManager($staticSegment, $this->exactly(count($segmentsIdsToSync)*4));
        $integrationEntityManager = $this->getIntegrationEntityManager($integration, $this->once());

        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepository')
            ->with(StaticSegment::class)
            ->willReturn($staticSegmentRepository);
        $doctrineHelper
            ->expects($this->exactly(2))
            ->method('getEntityManagerForClass')
            ->with(Integration::class)
            ->willReturn($integrationEntityManager);
        $doctrineHelper
            ->expects($this->exactly(count($segmentsIdsToSync)*2))
            ->method('getEntityManager')
            ->willReturn($segmentEntityManager);

        $expectedProcessorParameters = [
            'segments' => $segmentsIdsToSync,
            JobExecutor::JOB_CONTEXT_AGGREGATOR_TYPE => SelectiveContextAggregator::TYPE,
        ];
        $reverseSyncProcessor = $this->createReverseSyncProcessorMock();
        $reverseSyncProcessor
            ->expects($this->exactly(2))
            ->method('process')
            ->withConsecutive(
                [$integration, MemberConnector::TYPE, $expectedProcessorParameters],
                [$integration, StaticSegmentConnector::TYPE, $expectedProcessorParameters]
            );

        $processor = new ExportMailChimpProcessor(
            $doctrineHelper,
            $reverseSyncProcessor,
            $this->createStaticSegmentsMemberStateManagerMock(),
            $jobRunner,
            $this->createLoggerMock()
        );

        $message = new NullMessage();
        $message->setBody('{"integrationId":"'.$integrationId.'", "segmentsIds": ['.$segmentId.']}');
        $message->setMessageId($messageId);

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    /**
     * @return array
     */
    public function processMessageDataProvider()
    {
        return [
            'static segment is in not_synced status' => [
                'segmentId' => 1,
                'segmentIds' => [1],
                'syncStatus' => StaticSegment::STATUS_NOT_SYNCED,
            ],
            'static segment is in scheduled status' => [
                'segmentId' => 1,
                'segmentIds' => [1],
                'syncStatus' => StaticSegment::STATUS_SCHEDULED,
            ],
            'static segment is in scheduled_by_change status' => [
                'segmentId' => 1,
                'segmentIds' => [1],
                'syncStatus' => StaticSegment::STATUS_SCHEDULED_BY_CHANGE,
            ],
            'static segment is in synced status' => [
                'segmentId' => 1,
                'segmentIds' => [],
                'syncStatus' => StaticSegment::STATUS_SYNCED,
            ],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    private function createDoctrineHelperStub($integration = null)
    {
        $integrationEntityManager = $this->getIntegrationEntityManager($integration, $this->once());

        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper
            ->expects($this->any())
            ->method('getEntityManagerForClass')
            ->with(Integration::class)
            ->willReturn($integrationEntityManager);

        return $doctrineHelper;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ReverseSyncProcessor
     */
    private function createReverseSyncProcessorMock()
    {
        $reverseSyncProcessor =  $this->createMock(ReverseSyncProcessor::class);
        $reverseSyncProcessor
            ->expects($this->any())
            ->method('getLoggerStrategy')
            ->willReturn(new LoggerStrategy())
        ;

        return $reverseSyncProcessor;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StaticSegmentsMemberStateManager
     */
    private function createStaticSegmentsMemberStateManagerMock()
    {
        return $this->createMock(StaticSegmentsMemberStateManager::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JobRunner
     */
    private function createJobRunnerMock()
    {
        return $this->createMock(JobRunner::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private function createEntityManagerStub()
    {
        $configuration = new Configuration();

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock
            ->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($configuration)
        ;

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock)
        ;

        return $entityManagerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @param Integration|null                                 $integration
     * @param PHPUnit_Framework_MockObject_Matcher_Invocation  $invokeCountMatcher
     *
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getIntegrationEntityManager(
        Integration $integration = null,
        PHPUnit_Framework_MockObject_Matcher_Invocation $invokeCountMatcher
    ) {
        $integrationEntityManager = $this->createEntityManagerStub();
        $integrationEntityManager
            ->expects($invokeCountMatcher)
            ->method('find')
            ->with(Integration::class)
            ->willReturn($integration);

        return $integrationEntityManager;
    }

    /**
     * @param StaticSegment                                   $staticSegment
     * @param PHPUnit_Framework_MockObject_Matcher_Invocation $invokeCountMatcher
     *
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStaticSegmentEntityManager(
        StaticSegment $staticSegment,
        PHPUnit_Framework_MockObject_Matcher_Invocation $invokeCountMatcher
    ) {
        $segmentEntityManager = $this->createMock(EntityManagerInterface::class);
        $segmentEntityManager
            ->expects($invokeCountMatcher)
            ->method('persist')
            ->with($staticSegment);
        $segmentEntityManager
            ->expects($invokeCountMatcher)
            ->method('flush');

        return $segmentEntityManager;
    }
}
