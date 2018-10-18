<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\ImportExport;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;
use Oro\Bundle\MailChimpBundle\ImportExport\Writer\MemberWriter;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;
use Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadMemberData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class MemberWriterTest extends WebTestCase
{
    /**
     * @var MailChimpTransport|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $transport;

    /**
     * @var StepExecution|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stepExecution;

    public function setUp()
    {
        parent::setUp();

        $this->initClient();

        $this->loadFixtures([
            LoadMemberData::class
        ]);

        $this->transport = $this->createMock(MailChimpTransport::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->stepExecution->expects($this->any())
            ->method('getJobExecution')
            ->willReturn($this->createMock(JobExecution::class));

        $this->getContainer()->set('oro_mailchimp.transport.integration_transport', $this->transport);
    }

    public function testWrite()
    {
        /** @var Member $member1 */
        $member1 = $this->getReference('mailchimp:member_one');
        /** @var Member $member2 */
        $member2 = $this->getReference('mailchimp:member_two');
        /** @var SubscribersList $subscribersList */
        $subscribersList = $this->getReference('mailchimp:subscribers_list_one');

        $this->transport->expects($this->atLeastOnce())->method('init');
        $this->transport->expects($this->atLeastOnce())
            ->method('getListMergeVars')
            ->with($subscribersList->getOriginId())
            ->willReturn([
                'merge_fields' => [
                    ['name' => 'email', 'tag' => 'EMAIL', 'id' => 1],
                    ['name' => 'id', 'tag' => 'E_ID', 'id' => 2],
                    ['name' => 'firstName', 'tag' => 'FIRSTNAME', 'id' => 3],
                ],
            ]);
        $this->transport->expects($this->atLeastOnce())
            ->method('batchSubscribe')
            ->with([
                'list_id' => $subscribersList->getOriginId(),
                'members' => [
                    [
                        'email_address' => 'member1@example.com',
                        'status' => 'subscribed',
                        'merge_fields' => ['EMAIL' => 'member1@example.com', 'FIRSTNAME' => 'Antonio'],

                    ],
                    [
                        'email_address' => 'member2@example.com',
                        'status' => 'subscribed',
                        'merge_fields' => ['EMAIL' => 'member2@example.com', 'FIRSTNAME' => 'Michael'],
                    ],
                ],
                'double_optin' => false,
                'update_existing' => true,
            ])
            ->willReturn([
                'total_created' => 0,
                'total_updated' => 2,
                'error_count' => 0
            ]);

        /** @var MemberWriter $writer */
        $writer = $this->getContainer()->get('oro_mailchimp.importexport.writer.member');
        $writer->setStepExecution($this->stepExecution);

        $this->assertEquals([
            'EMAIL' => 'member1@example.com',
            'FIRSTNAME' => 'Antonio',
            'LASTNAME' => 'Banderas'
        ], $member1->getMergeVarValues());

        $writer->write([$member1, $member2]);

        $em = $this->getContainer()->get('oro_entity.doctrine_helper')->getEntityManagerForClass(Member::class);
        $member1 = $em->find(Member::class, $member1->getId());
        $member2 = $em->find(Member::class, $member2->getId());

        $this->assertEquals([
            'EMAIL' => 'member1@example.com',
            'FIRSTNAME' => 'Antonio',
        ], $member1->getMergeVarValues());

        $this->assertEquals([
            'EMAIL' => 'member2@example.com',
            'FIRSTNAME' => 'Michael'
        ], $member2->getMergeVarValues());

        $subscribersList = $em->find(SubscribersList::class, $subscribersList->getId());
        $this->assertEquals([
            ['name' => 'email', 'tag' => 'EMAIL', 'id' => 1],
            ['name' => 'id', 'tag' => 'E_ID', 'id' => 2],
            ['name' => 'firstName', 'tag' => 'FIRSTNAME', 'id' => 3],
        ], $subscribersList->getMergeVarConfig());
    }
}
