<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport\Iterator;

use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\CampaignIterator;

class CampaignIteratorTest extends \PHPUnit_Framework_TestCase
{
    const TEST_BATCH_SIZE = 2;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(
            'Oro\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClient'
        )->setMethods(['getCampaigns'])
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

        $this->client->expects($this->exactly(count($campaignValueMap)))
            ->method('getCampaigns')
            ->will($this->returnValueMap($campaignValueMap));

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
                        ['start' => 0, 'limit' => 2],
                        [
                            'total' => 3,
                            'data' => [
                                ['id' => '3d21b11eb1', 'name' => 'Campaign 1'],
                                ['id' => '3d21b11eb2', 'name' => 'Campaign 2'],
                            ]
                        ]
                    ],
                    [
                        ['start' => 1, 'limit' => 2],
                        [
                            'total' => 3,
                            'data' => [
                                ['id' => '3d21b11eb3', 'name' => 'Campaign 3'],
                            ]
                        ]
                    ]
                ],
                'expected' => [
                    ['id' => '3d21b11eb1', 'name' => 'Campaign 1'],
                    ['id' => '3d21b11eb2', 'name' => 'Campaign 2'],
                    ['id' => '3d21b11eb3', 'name' => 'Campaign 3']
                ]
            ],
            'two pages with filters' => [
                'filters' => ['status' => 'sent'],
                'listValueMap' => [
                    [
                        ['start' => 0, 'limit' => 2, 'filters' => ['status' => 'sent']],
                        [
                            'total' => 3,
                            'data' => [
                                ['id' => '3d21b11eb1', 'name' => 'Campaign 1'],
                                ['id' => '3d21b11eb2', 'name' => 'Campaign 2'],
                            ]
                        ]
                    ],
                    [
                        ['start' => 1, 'limit' => 2, 'filters' => ['status' => 'sent']],
                        [
                            'total' => 3,
                            'data' => [
                                ['id' => '3d21b11eb3', 'name' => 'Campaign 3'],
                            ]
                        ]
                    ]
                ],
                'expected' => [
                    ['id' => '3d21b11eb1', 'name' => 'Campaign 1'],
                    ['id' => '3d21b11eb2', 'name' => 'Campaign 2'],
                    ['id' => '3d21b11eb3', 'name' => 'Campaign 3']
                ]
            ],
            'empty' => [
                'filters' => [],
                'listValueMap' => [
                    [
                        ['start' => 0, 'limit' => 2],
                        [
                            'total' => 0,
                            'data' => []
                        ]
                    ]
                ],
                'expected' => []
            ],
        ];
    }
}
