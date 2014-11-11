<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\BatchJob;

use Doctrine\Common\Persistence\ManagerRegistry;
use Guzzle\Http\Message\Response;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MailChimpBundle\Provider\Connector\MemberActivityConnector;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClientFactory;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

/**
 * @dbIsolation
 */
class MemberActivityImportTest extends WebTestCase
{
    /**
     * @var JobExecutor
     */
    protected $jobExecutor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MailChimpClientFactory
     */
    protected $clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MailChimpClient
     */
    protected $mailChimpClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    public function setUp()
    {
        $this->initClient();

        $this->jobExecutor = $this->getContainer()->get('oro_importexport.job_executor');

        $this->clientFactory = $this
            ->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClientFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mailChimpClient = $this
            ->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient')
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->mailChimpClient));

        $this->managerRegistry = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = $this
            ->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Entity\Repository\CampaignRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerRegistry
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->getContainer()->set(
            'orocrm_mailchimp.transport.integration_transport',
            new MailChimpTransport($this->clientFactory, $this->managerRegistry)
        );

        $this->loadFixtures(
            [
                'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadCampaignData',
                'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadMemberData',
            ]
        );
    }

    /**
     * @param int $addCount
     * @param array $data
     *
     * @dataProvider memberActivityDataProvider
     */
    public function testRunJob($addCount, array $data)
    {
        $this->repository
            ->expects($this->any())
            ->method('getSentCampaigns')
            ->will($this->returnValue(new \ArrayIterator([$this->getReference('mailchimp_campaign')])));

        $this->mailChimpClient
            ->expects($this->any())
            ->method('export')
            ->will($this->returnValue(new Response('200', null, json_encode($data))));

        $jobResult = $this->jobExecutor->executeJob(
            ProcessorRegistry::TYPE_IMPORT,
            MemberActivityConnector::JOB_IMPORT,
            [
                ProcessorRegistry::TYPE_IMPORT => [
                    'channel' => $this->getReference('mailchimp_transport:test_transport1')->getId(),
                    'channelType' => $this->getReference('mailchimp_transport:test_transport1')->getType()
                ]
            ]
        );

        $this->assertTrue($jobResult->isSuccessful());
        $this->assertEquals($addCount, $jobResult->getContext()->getAddCount());
    }

    /**
     * @return array
     */
    public function memberActivityDataProvider()
    {
        return [
            'one' => [
                2,
                [
                    'member1@example.com' => [
                        [
                            'action' => 'click',
                            'timestamp' => '2014-11-10 14:46:09',
                            'url' => 'http://inspiration.mailchimp.com',
                            'ip' => '0.0.0.0'
                        ],
                        [
                            'action' => 'open',
                            'timestamp' => '2014-11-10 14:46:09',
                            'url' => null,
                            'ip' => '0.0.0.0'
                        ]
                    ],
                ]
            ],
            'two' => [
                4,
                [
                    'member1@example.com' => [
                        [
                            'action' => 'click',
                            'timestamp' => '2014-11-10 14:46:09',
                            'url' => 'http://inspiration.mailchimp.com',
                            'ip' => '0.0.0.0'
                        ],
                        [
                            'action' => 'open',
                            'timestamp' => '2014-11-10 14:46:09',
                            'url' => null,
                            'ip' => '0.0.0.0'
                        ]
                    ],
                    'member2@example.com' => [
                        [
                            'action' => 'click',
                            'timestamp' => '2014-11-10 14:46:09',
                            'url' => 'http://inspiration.mailchimp.com',
                            'ip' => '0.0.0.0'
                        ],
                        [
                            'action' => 'open',
                            'timestamp' => '2014-11-10 14:46:09',
                            'url' => null,
                            'ip' => '0.0.0.0'
                        ]
                    ]
                ]
            ],
            'skip_without_member' => [
                2,
                [
                    'member1@example.com' => [
                        [
                            'action' => 'click',
                            'timestamp' => '2014-11-10 14:46:09',
                            'url' => 'http://inspiration.mailchimp.com',
                            'ip' => '0.0.0.0'
                        ],
                        [
                            'action' => 'open',
                            'timestamp' => '2014-11-10 14:46:09',
                            'url' => null,
                            'ip' => '0.0.0.0'
                        ]
                    ],
                    'not_existing@example.com' => [
                        [
                            'action' => 'click',
                            'timestamp' => '2014-11-10 14:46:09',
                            'url' => 'http://inspiration.mailchimp.com',
                            'ip' => '0.0.0.0'
                        ],
                        [
                            'action' => 'open',
                            'timestamp' => '2014-11-10 14:46:09',
                            'url' => null,
                            'ip' => '0.0.0.0'
                        ]
                    ]
                ]
            ]
        ];
    }
}
