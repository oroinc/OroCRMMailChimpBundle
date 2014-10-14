<?php

namespace ForumGroup\Bundle\SalesForceMigrationBundle\Tests\Unit\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\ListIterator;

class ListIteratorTest extends \PHPUnit_Framework_TestCase
{
    const TEST_BATCH_SIZE = 2;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    /**
     * @var ListIterator
     */
    protected $iterator;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(
            'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClient'
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
            'tow pages' => [
                'listValueMap' => [
                    [
                        ['start' => 0, 'limit' => 2],
                        [
                            'total' => 3,
                            'data' => [
                                ['id' => '3d21b11eb1', 'name' => 'List 1'],
                                ['id' => '3d21b11eb2', 'name' => 'List 2'],
                            ]
                        ]
                    ],
                    [
                        ['start' => 1, 'limit' => 2],
                        [
                            'total' => 3,
                            'data' => [
                                ['id' => '3d21b11eb3', 'name' => 'List 3'],
                            ]
                        ]
                    ]
                ],
                'mergeVarValueMap' => [
                    [
                        ['id' => ['3d21b11eb1', '3d21b11eb2']],
                        ['success_count' => 0, 'data' => []]
                    ],
                    [
                        ['id' => ['3d21b11eb3']],
                        ['total' => 0, 'data' => []]
                    ]
                ],
                'expected' => [
                    ['id' => '3d21b11eb1', 'name' => 'List 1', 'merge_vars' => []],
                    ['id' => '3d21b11eb2', 'name' => 'List 2', 'merge_vars' => []],
                    ['id' => '3d21b11eb3', 'name' => 'List 3', 'merge_vars' => []]
                ]
            ],
            'with merge vars' => [
                'listValueMap' => [
                    [
                        ['start' => 0, 'limit' => 2],
                        [
                            'total' => 2,
                            'data' => [
                                ['id' => '3d21b11eb1', 'name' => 'List 1'],
                                ['id' => '3d21b11eb2', 'name' => 'List 2'],
                            ]
                        ]
                    ]
                ],
                'mergeVarValueMap' => [
                    [
                        ['id' => ['3d21b11eb1', '3d21b11eb2']],
                        [
                            'success_count' => 2,
                            'data' => [
                                [
                                    'id' => '3d21b11eb1', 'name' => 'List 1',
                                    'merge_vars' => [
                                        ['name' => 'Email Address', 'tag' => 'EMAIL']
                                    ],
                                ],
                                [
                                    'id' => '3d21b11eb2', 'name' => 'List 2',
                                    'merge_vars' => [
                                        ['name' => 'Email Address', 'tag' => 'EMAIL'],
                                        ['name' => 'First Name', 'tag' => 'FNAME'],
                                        ['name' => 'Last Name', 'tag' => 'LNAME'],
                                    ],
                                ],
                            ]
                        ]
                    ]
                ],
                'expected' => [
                    [
                        'id' => '3d21b11eb1',
                        'name' => 'List 1',
                        'merge_vars' => [
                            ['name' => 'Email Address', 'tag' => 'EMAIL']
                        ],
                    ],
                    [
                        'id' => '3d21b11eb2',
                        'name' => 'List 2',
                        'merge_vars' => [
                            ['name' => 'Email Address', 'tag' => 'EMAIL'],
                            ['name' => 'First Name', 'tag' => 'FNAME'],
                            ['name' => 'Last Name', 'tag' => 'LNAME'],
                        ],
                    ],
                ]
            ],
            'empty' => [
                'listValueMap' => [
                    [
                        ['start' => 0, 'limit' => 2],
                        [
                            'total' => 0,
                            'data' => []
                        ]
                    ]
                ],
                'mergeVarValueMap' => [],
                'expected' => []
            ],
        ];
    }
}
