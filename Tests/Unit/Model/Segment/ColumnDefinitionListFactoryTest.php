<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\Segment;

use Oro\Bundle\SegmentBundle\Entity\Segment;

use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionListFactory;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class ColumnDefinitionListFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ColumnDefinitionListFactory
     */
    protected $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MarketingList
     */
    protected $marketingList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Segment
     */
    protected $segment;

    protected function setUp()
    {
        $this->marketingList = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $this->segment = $this
            ->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')
            ->disableOriginalConstructor()
            ->getMock();
        $this->marketingList->expects($this->any())->method('getSegment')->will($this->returnValue($this->segment));
        $this->factory = new ColumnDefinitionListFactory();
    }

    protected function tearDown()
    {
        unset($this->marketingList);
        unset($this->segment);
        unset($this->factory);
    }

    public function testCreate()
    {
        $object = $this->factory->create($this->marketingList);

        $this->assertInstanceOf('OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionList', $object);
    }
}
