<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\Segment;

use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\AbandonedCartBundle\Model\MarketingList\AbandonedCartSource;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionListFactory;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListSourceInterface;

class ColumnDefinitionListFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ColumnDefinitionListFactory
     */
    private $factory;

    /**
     * @var MarketingList
     */
    private $marketingList;

    /**
     * @var Segment
     */
    private $segment;

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

    public function testCreateForNativeMarketingList()
    {
        $this->marketingList->expects($this->once())->method('getSource')
            ->will($this->returnValue(MarketingListSourceInterface::DEFAULT_SOURCE_CODE));

        $object = $this->factory->create($this->marketingList);

        $this->assertInstanceOf('OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionList', $object);
        $this->assertNotInstanceOf('OroCRM\Bundle\MailChimpBundle\Model\Segment\CartColumnDefinitionList', $object);
    }

    public function testCreateForAbandonedCartMarketingList()
    {
        $this->marketingList->expects($this->once())->method('getSource')
            ->will($this->returnValue(AbandonedCartSource::SOURCE_CODE));

        $object = $this->factory->create($this->marketingList);

        $this->assertInstanceOf('OroCRM\Bundle\MailChimpBundle\Model\Segment\CartColumnDefinitionList', $object);
    }
}
