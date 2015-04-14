<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\AutomationCampaignIterator;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\CampaignIterator;

class AutomationCampaignIteratorTest extends \PHPUnit_Framework_TestCase
{
    const TEST_BATCH_SIZE = 2;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $campaignIterator;

    protected function setUp()
    {
        $this->client = $this
            ->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient')
            ->setMethods(['getCampaigns'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->campaignIterator = new CampaignIterator($this->client, array(), self::TEST_BATCH_SIZE);
    }

    protected function tearDown()
    {
        unset($this->client, $this->campaignIterator);
    }

    /**
     * @dataProvider iteratorDataProvider
     * @param array $campaignValueMap
     * @param array $expected
     */
    public function testIteratorWorks(array $campaignValueMap, array $expected)
    {
        $automationCampaignIterator = new AutomationCampaignIterator($this->campaignIterator);

        $this->client->expects($this->exactly(count($campaignValueMap)))
            ->method('getCampaigns')
            ->will($this->returnValueMap($campaignValueMap));

        $actual = [];
        foreach ($automationCampaignIterator as $key => $value) {
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
            'two pages with filters' => [
                'listValueMap' => [
                    [
                        ['start' => 0, 'limit' => 2, 'filters' => ['uses_segment' => true, 'type' => 'automation']],
                        [
                            'total' => 3,
                            'data' => [
                                ['id' => '3d21b11eb1', 'name' => 'Automation Campaign 1'],
                                ['id' => '3d21b11eb2', 'name' => 'Automation Campaign 2'],
                            ]
                        ]
                    ],
                    [
                        ['start' => 1, 'limit' => 2, 'filters' => ['uses_segment' => true, 'type' => 'automation']],
                        [
                            'total' => 3,
                            'data' => [
                                ['id' => '3d21b11eb3', 'name' => 'Automation Campaign 3'],
                            ]
                        ]

                    ]
                ],
                'expected' => [
                    ['id' => '3d21b11eb1', 'name' => 'Automation Campaign 1'],
                    ['id' => '3d21b11eb2', 'name' => 'Automation Campaign 2'],
                    ['id' => '3d21b11eb3', 'name' => 'Automation Campaign 3']
                ]
            ],
            'empty' => [
                'listValueMap' => [
                    [
                        ['start' => 0, 'limit' => 2, 'filters' => ['uses_segment' => true, 'type' => 'automation']],
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
