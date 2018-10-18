<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport\Iterator;

use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\CampaignIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class CampaignIteratorTest extends \PHPUnit\Framework\TestCase
{
    const TEST_BATCH_SIZE = 2;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $client;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(MailChimpClient::class)
            ->setMethods(['getCampaigns', 'getCampaignReport'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $filters
     * @return CampaignIterator
     */
    protected function createCampaignIterator(array $filters)
    {
        return new CampaignIterator($this->client, $filters, self::TEST_BATCH_SIZE);
    }

    /**
     * @dataProvider iteratorDataProvider
     * @param array $filters
     * @param array $campaignValueMap
     * @param array $expected
     */
    public function testIteratorWorks(array $filters, array $campaignValueMap, array $expected)
    {
        $iterator = $this->createCampaignIterator($filters);

        $this->client
            ->expects($this->exactly(count($campaignValueMap)))
            ->method('getCampaigns')
            ->will($this->returnValueMap($campaignValueMap));

        $this->client
            ->expects($this->any())
            ->method('getCampaignReport')
            ->willReturn([]);

        $actual = [];
        foreach ($iterator as $key => $value) {
            $actual[$key] = $value;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function iteratorDataProvider()
    {
        return [
            'two pages without filters' => [
                'filters' => [],
                'listValueMap' => [
                    [
                        ['offset' => 0, 'count' => 2],
                        [
                            'total_items' => 3,
                            'campaigns' => [
                                ['id' => '3d21b11eb1', 'name' => 'Campaign 1', 'report' => []],
                                ['id' => '3d21b11eb2', 'name' => 'Campaign 2', 'report' => []],
                            ]
                        ]
                    ],
                    [
                        ['offset' => 1, 'count' => 2],
                        [
                            'total_items' => 3,
                            'campaigns' => [
                                ['id' => '3d21b11eb3', 'name' => 'Campaign 3', 'report' => []],
                            ]
                        ]
                    ]
                ],
                'expected' => [
                    ['id' => '3d21b11eb1', 'name' => 'Campaign 1', 'report' => []],
                    ['id' => '3d21b11eb2', 'name' => 'Campaign 2', 'report' => []],
                    ['id' => '3d21b11eb3', 'name' => 'Campaign 3', 'report' => []]
                ]
            ],
            'two pages with filters' => [
                'filters' => ['status' => 'sent'],
                'listValueMap' => [
                    [
                        ['offset' => 0, 'count' => 2, 'status' => 'sent'],
                        [
                            'total_items' => 3,
                            'campaigns' => [
                                ['id' => '3d21b11eb1', 'name' => 'Campaign 1', 'report' => []],
                                ['id' => '3d21b11eb2', 'name' => 'Campaign 2', 'report' => []],
                            ]
                        ]
                    ],
                    [
                        ['offset' => 1, 'count' => 2, 'status' => 'sent'],
                        [
                            'total_items' => 3,
                            'campaigns' => [
                                ['id' => '3d21b11eb3', 'name' => 'Campaign 3', 'report' => []],
                            ]
                        ]
                    ]
                ],
                'expected' => [
                    ['id' => '3d21b11eb1', 'name' => 'Campaign 1', 'report' => []],
                    ['id' => '3d21b11eb2', 'name' => 'Campaign 2', 'report' => []],
                    ['id' => '3d21b11eb3', 'name' => 'Campaign 3', 'report' => []]
                ]
            ],
            'empty' => [
                'filters' => [],
                'listValueMap' => [
                    [
                        ['offset' => 0, 'count' => 2],
                        [
                            'total_items' => 0,
                            'campaigns' => []
                        ]
                    ]
                ],
                'expected' => []
            ],
        ];
    }
}
