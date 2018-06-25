<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport\Iterator;

use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\MmbrExtdMergeVarIterator;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListProvider;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Component\Testing\Unit\EntityTrait;

class MmbrExtdMergeVarIteratorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @param \Iterator $mainIterator
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|MmbrExtdMergeVarIterator
     */
    protected function createIterator(\Iterator $mainIterator)
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|MmbrExtdMergeVarIterator $iterator */
        $iterator = $this->getMockBuilder(MmbrExtdMergeVarIterator::class)
            ->setMethods(['createBufferedIterator'])
            ->setConstructorArgs(
                [
                    $this->createMock(MarketingListProvider::class),
                    $this->createMock(OwnershipMetadataProviderInterface::class),
                    'removedItemClassName',
                    'unsubscribedItemClassName'
                ]
            )
            ->allowMockingUnknownTypes()
            ->getMock();
        $iterator->setMainIterator($mainIterator);

        return $iterator;
    }

    /**
     * @dataProvider optionsDataProvider
     *
     * @param \Iterator $mainIterator
     * @param array $values
     * @param array $expected
     */
    public function testIterator(\Iterator $mainIterator, $values, $expected)
    {
        $iterator = $this->createIterator($mainIterator);

        $iterator
            ->method('createBufferedIterator')
            ->will($this->returnValue(new \ArrayIterator($values)));

        $actual = [];
        foreach ($iterator as $value) {
            $actual[] = $value;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function optionsDataProvider()
    {
        $subscribersListId = 1;
        $staticSegmentId   = 1;

        $subscribersList = $this->getEntity(SubscribersList::class, ['id' => $subscribersListId]);
        $staticSegment   = $this->getEntity(
            StaticSegment::class,
            [
                'id'=> $staticSegmentId,
                'subscribersList' => $subscribersList
            ]
        );
        $mainIterator   = new \ArrayIterator([$staticSegment]);

        return [
            'without array' => [
                'mainIterator' => $mainIterator,
                'values' => [101, 102],
                'expected' => [101, 102]
            ],
            'with array' => [
                'mainIterator' => $mainIterator,
                'values' => [
                    [
                        'id' => 1,
                        'member_id' => 101
                    ],
                    [
                        'id' => 2,
                        'member_id' => 102
                    ]
                ],
                'expected' => [
                    [
                        'member_id' => 101,
                        'subscribersList_id' => $subscribersListId,
                        'static_segment_id' => $staticSegmentId
                    ],
                    [
                        'member_id' => 102,
                        'subscribersList_id' => $subscribersListId,
                        'static_segment_id' => $staticSegmentId
                    ]
                ]
            ]
        ];
    }
}
