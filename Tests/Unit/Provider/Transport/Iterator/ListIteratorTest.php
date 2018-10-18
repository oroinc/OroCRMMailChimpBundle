<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport\Iterator;

use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\ListIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class ListIteratorTest extends \PHPUnit\Framework\TestCase
{
    const TEST_BATCH_SIZE = 2;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $client;

    /**
     * @var ListIterator
     */
    protected $iterator;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(
            MailChimpClient::class
        )->setMethods(['getLists', 'getListMergeVars'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->iterator = new ListIterator($this->client, self::TEST_BATCH_SIZE);
    }

    /**
     * @dataProvider iteratorDataProvider
     * @param array $listValueMap
     * @param array $mergeVarValueMap
     * @param array $expected
     */
    public function testIteratorWorks(array $listValueMap, array $mergeVarValueMap, array $expected)
    {
        $this->client->expects($this->exactly(count($listValueMap)))
            ->method('getLists')
            ->will($this->returnValueMap($listValueMap));


        $this->client->expects($this->exactly(count($mergeVarValueMap)))
            ->method('getListMergeVars')
            ->will($this->returnValueMap($mergeVarValueMap));

        $actual = [];
        foreach ($this->iterator as $key => $value) {
            $actual[$key] = $value;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function iteratorDataProvider()
    {
        return [
            'two pages' => [
                'listValueMap' => [
                    [
                        ['offset' => 0, 'count' => 2],
                        [
                            'total_items' => 3,
                            'lists' => [
                                ['id' => '3d21b11eb1', 'name' => 'List 1'],
                                ['id' => '3d21b11eb2', 'name' => 'List 2'],
                            ]
                        ]
                    ],
                    [
                        ['offset' => 1, 'count' => 2],
                        [
                            'total_items' => 3,
                            'lists' => [
                                ['id' => '3d21b11eb3', 'name' => 'List 3'],
                            ]
                        ]
                    ]
                ],
                'mergeVarValueMap' => [
                    [
                        ['id' => '3d21b11eb1'],
                        ['success_count' => 0, 'merge_fields' => []]
                    ],
                    [
                        ['id' => '3d21b11eb2'],
                        ['total_items' => 0, 'merge_fields' => []]
                    ],
                    [
                        ['id' => '3d21b11eb3'],
                        ['total_items' => 0, 'merge_fields' => []]
                    ]
                ],
                'expected' => [
                    ['id' => '3d21b11eb1', 'name' => 'List 1', 'merge_fields' => []],
                    ['id' => '3d21b11eb2', 'name' => 'List 2', 'merge_fields' => []],
                    ['id' => '3d21b11eb3', 'name' => 'List 3', 'merge_fields' => []]
                ]
            ],
            'with merge vars' => [
                'listValueMap' => [
                    [
                        ['offset' => 0, 'count' => 2],
                        [
                            'total_items' => 2,
                            'lists' => [
                                ['id' => '3d21b11eb1', 'name' => 'List 1'],
                                ['id' => '3d21b11eb2', 'name' => 'List 2'],
                            ]
                        ]
                    ]
                ],
                'mergeVarValueMap' => [
                    [
                        '3d21b11eb1',
                        [
                            'total_items' => 2,
                            'merge_fields' => [
                                [
                                    'name' => 'Email Address',
                                    'tag' => 'EMAIL',
                                ],
                            ]
                        ],
                    ],
                    [
                        '3d21b11eb2',
                        [
                            'total_items' => 2,
                            'merge_fields' => [
                                [
                                    'name' => 'Email Address',
                                    'tag' => 'EMAIL'
                                ],
                                [
                                    'name' => 'First Name',
                                    'tag' => 'FNAME'
                                ],
                                [
                                    'name' => 'Last Name',
                                    'tag' => 'LNAME'
                                ],
                            ],
                        ]
                    ]
                ],
                'expected' => [
                    [
                        'id' => '3d21b11eb1',
                        'name' => 'List 1',
                        'merge_fields' => [
                            [
                                'name' => 'Email Address',
                                'tag' => 'EMAIL'
                            ]
                        ],
                    ],
                    [
                        'id' => '3d21b11eb2',
                        'name' => 'List 2',
                        'merge_fields' => [
                            [
                                'name' => 'Email Address',
                                'tag' => 'EMAIL'
                            ],
                            [
                                'name' => 'First Name',
                                'tag' => 'FNAME'
                            ],
                            [
                                'name' => 'Last Name',
                                'tag' => 'LNAME'
                            ],
                        ],
                    ],
                ]
            ],
            'empty' => [
                'listValueMap' => [
                    [
                        ['offset' => 0, 'count' => 2],
                        [
                            'total_items' => 0,
                            'lists' => []
                        ]
                    ]
                ],
                'mergeVarValueMap' => [],
                'expected' => []
            ],
        ];
    }
}
