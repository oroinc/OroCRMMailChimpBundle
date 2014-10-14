<?php

namespace ForumGroup\Bundle\SalesForceMigrationBundle\Tests\Unit\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class MemberIteratorTest extends \PHPUnit_Framework_TestCase
{
    const TEST_LIST_ID = 100;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(
            'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClient'
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * @param \Iterator $subscriberLists
     * @param array $parameters
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createIterator(\Iterator $subscriberLists, array $parameters)
    {
        return $this->getMock(
            'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\Iterator\\MemberIterator',
            ['createExportIterator'],
            [$subscriberLists, $this->client, $parameters]
        );
    }

    /**
     * @dataProvider iteratorDataProvider
     * @param array $parameters
     * @param array $expectedValueMap
     * @param array $expected
     */
    public function testIteratorWorks(array $parameters, array $expectedValueMap, array $expected)
    {
        $list = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\SubscribersList');
        $list->expects($this->atLeastOnce())
            ->method('getOriginId')
            ->will($this->returnValue(self::TEST_LIST_ID));

        $subscriberLists = new \ArrayIterator([$list]);

        $iterator = $this->createIterator($subscriberLists, $parameters);

        $iterator->expects($this->exactly(count($expectedValueMap)))
            ->method('createExportIterator')
            ->will($this->returnValueMap($expectedValueMap));

        $actual = [];
        foreach ($iterator as $key => $value) {
            $actual[$key] = $value;
        }

        $this->assertEquals($expected, $actual);
    }

    public function iteratorDataProvider()
    {
        $memberFoo = ['email' => 'foo@example.com'];
        $memberBar = ['email' => 'bar@example.com'];
        $memberBaz = ['email' => 'baz@example.com'];

        return [
            'empty status' => [
                'parameters' => ['include_empty' => true],
                'expectedValueMap' => [
                    [
                        MailChimpClient::EXPORT_LIST,
                        ['include_empty' => true, 'status' => Member::STATUS_SUBSCRIBED, 'id' => self::TEST_LIST_ID],
                        new \ArrayIterator([$memberFoo, $memberBar, $memberBaz])
                    ]
                ],
                'expected' => [
                    $this->passMember($memberFoo, Member::STATUS_SUBSCRIBED),
                    $this->passMember($memberBar, Member::STATUS_SUBSCRIBED),
                    $this->passMember($memberBaz, Member::STATUS_SUBSCRIBED),
                ]
            ],
            'single status' => [
                'parameters' => ['status' => Member::STATUS_UNSUBSCRIBED],
                'expectedValueMap' => [
                    [
                        MailChimpClient::EXPORT_LIST,
                        ['status' => Member::STATUS_UNSUBSCRIBED, 'id' => self::TEST_LIST_ID],
                        new \ArrayIterator([$memberFoo, $memberBar, $memberBaz])
                    ]
                ],
                'expected' => [
                    $this->passMember($memberFoo, Member::STATUS_UNSUBSCRIBED),
                    $this->passMember($memberBar, Member::STATUS_UNSUBSCRIBED),
                    $this->passMember($memberBaz, Member::STATUS_UNSUBSCRIBED),
                ]
            ],
            'multiple statuses' => [
                'parameters' => ['status' => [Member::STATUS_SUBSCRIBED, Member::STATUS_UNSUBSCRIBED]],
                'expectedValueMap' => [
                    [
                        MailChimpClient::EXPORT_LIST,
                        ['status' => Member::STATUS_SUBSCRIBED, 'id' => self::TEST_LIST_ID],
                        new \ArrayIterator([$memberFoo, $memberBar])
                    ],
                    [
                        MailChimpClient::EXPORT_LIST,
                        ['status' => Member::STATUS_UNSUBSCRIBED, 'id' => self::TEST_LIST_ID],
                        new \ArrayIterator([$memberBaz])
                    ],
                ],
                'expected' => [
                    $this->passMember($memberFoo, Member::STATUS_SUBSCRIBED),
                    $this->passMember($memberBar, Member::STATUS_SUBSCRIBED),
                    $this->passMember($memberBaz, Member::STATUS_UNSUBSCRIBED),
                ]
            ],
        ];
    }

    protected function passMember(array $member, $status)
    {
        $member['list_id'] = self::TEST_LIST_ID;
        $member['status'] = $status;
        return $member;
    }
}
