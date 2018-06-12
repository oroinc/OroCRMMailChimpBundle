<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use Oro\Bundle\MailChimpBundle\ImportExport\Writer\StaticSegmentExportWriter;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;
use Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentMemberData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Psr\Log\LoggerInterface;

class StaticSegmentExportWriterTest extends WebTestCase
{
    /**
     * @var MailChimpTransport|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transport;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var StaticSegmentExportWriter
     */
    private $staticSegmentExportWriter;

    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures([LoadStaticSegmentMemberData::class]);

        /** @var StepExecution|\PHPUnit_Framework_MockObject_MockObject $stepExecution */
        $stepExecution = $this->createMock(StepExecution::class);
        $stepExecution->expects($this->any())
            ->method('getJobExecution')
            ->willReturn($this->createMock(JobExecution::class));

        $this->transport = $this->createMock(MailChimpTransport::class);
        $this->transport->expects($this->once())->method('init');
        self::getContainer()->set('oro_mailchimp.transport.integration_transport', $this->transport);

        $this->logger = $this->createMock(LoggerInterface::class);
        self::getContainer()->set('oro_integration.logger.strategy', $this->logger);

        $this->context = $this->createMock(ContextInterface::class);
        $contextRegistry = $this->createMock(ContextRegistry::class);
        $contextRegistry->expects($this->any())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->willReturn($this->context);
        self::getContainer()->set('oro_importexport.context_registry', $contextRegistry);

        $this->staticSegmentExportWriter = self::getContainer()
            ->get('oro_mailchimp.importexport.writer.static_segment');
        $this->staticSegmentExportWriter->setStepExecution($stepExecution);
    }

    protected function tearDown()
    {
        self::getContainer()->set('oro_mailchimp.transport.integration_transport', null);
        self::getContainer()->set('oro_integration.logger.strategy', null);
        self::getContainer()->set('oro_importexport.context_registry', null);
        $this->staticSegmentExportWriter->restoreStepExecution();
    }

    public function testMemberDropped()
    {
        /** @var StaticSegment $staticSegment */
        $staticSegment = $this->getReference('mailchimp:segment_one');
        $staticSegmentOriginId = 12345;
        /** @var StaticSegmentMember $staticSegmentMember */
        $staticSegmentMember = $this->getReference('mailchimp:static-segment-member3');

        $this->transport->expects($this->once())
            ->method('addStaticListSegment')
            ->with([
                'id' => $staticSegment->getSubscribersList()->getOriginId(),
                'name' => $staticSegment->getName(),
            ])
            ->willReturn(['id' => $staticSegmentOriginId]);
        $this->context->expects($this->once())->method('incrementAddCount');

        $this->logger->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                [
                    sprintf('StaticSegment with id "%s" added', $staticSegmentOriginId)
                ],
                [
                    sprintf(
                        'Member with id "%s" and email "%s" got "%s" state',
                        $staticSegmentMember->getId(),
                        $staticSegmentMember->getMember()->getEmail(),
                        StaticSegmentMember::STATE_DROP
                    ),
                ]
            );

        $this->staticSegmentExportWriter->write([$staticSegment]);

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->refresh($staticSegmentMember);
        $this->assertEquals($staticSegmentOriginId, $staticSegment->getOriginId());
        $this->assertEquals(StaticSegmentMember::STATE_DROP, $staticSegmentMember->getState());

        $entityManager->refresh($staticSegmentMember->getMember());
        $this->assertEquals(Member::STATUS_DROPPED, $staticSegmentMember->getMember()->getStatus());
    }
}
