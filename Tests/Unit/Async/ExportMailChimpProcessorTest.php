<?php
namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Async;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\Testing\ClassExtensionTrait;
use OroCRM\Bundle\MailChimpBundle\Async\ExportMailChimpProcessor;
use OroCRM\Bundle\MailChimpBundle\Async\Topics;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentsMemberStateManager;

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
            $this->createDoctrineHelperMock(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock()
        );
    }

    public function testShouldSubscribeOnExportMailChimpSegmentsTopic()
    {
        $this->assertEquals([Topics::EXPORT_MAIL_CHIMP_SEGMENTS], ExportMailChimpProcessor::getSubscribedTopics());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The message invalid. It must have integrationId set
     */
    public function testThrowIfMessageBodyMissIntegrationId()
    {
        $processor = new ExportMailChimpProcessor(
            $this->createDoctrineHelperMock(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock()
        );

        $message = new NullMessage();
        $message->setBody('[]');

        $processor->process($message, new NullSession());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The message invalid. It must have segmentsIds set
     */
    public function testThrowIfMessageBodyMissSegmentsIds()
    {
        $processor = new ExportMailChimpProcessor(
            $this->createDoctrineHelperMock(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock()
        );

        $message = new NullMessage();
        $message->setBody('{"integrationId":1}');

        $processor->process($message, new NullSession());
    }
    
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The malformed json given.
     */
    public function testThrowIfMessageBodyInvalidJson()
    {
        $processor = new ExportMailChimpProcessor(
            $this->createDoctrineHelperMock(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock()
        );

        $message = new NullMessage();
        $message->setBody('[}');

        $processor->process($message, new NullSession());
    }

    public function testShouldRunUniqueJob()
    {
        $jobRunner = $this->createJobRunnerMock();
        $jobRunner->expects($this->once())
            ->method('runUnique');

        $processor = new ExportMailChimpProcessor(
            $this->createDoctrineHelperMock(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $jobRunner
        );

        $message = new NullMessage();
        $message->setBody('{"integrationId":"theIntegrationId", "segmentsIds": 1}');
        $message->setMessageId('theMessageId');

        $processor->process($message, new NullSession());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    private function createDoctrineHelperMock()
    {
        return $this->getMock(DoctrineHelper::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ReverseSyncProcessor
     */
    private function createReverseSyncProcessorMock()
    {
        return $this->getMock(ReverseSyncProcessor::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StaticSegmentsMemberStateManager
     */
    private function createStaticSegmentsMemberStateManagerMock()
    {
        return $this->getMock(StaticSegmentsMemberStateManager::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JobRunner
     */
    private function createJobRunnerMock()
    {
        return $this->getMock(JobRunner::class, [], [], '', false);
    }
}
