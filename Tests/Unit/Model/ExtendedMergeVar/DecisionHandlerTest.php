<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\ExtendedMergeVar;

use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\DecisionHandler;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListSourceInterface;

class DecisionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DecisionHandler
     */
    private $handler;

    /**
     * @var MarketingListSourceInterface
     */
    private $source;

    protected function setUp()
    {
        $this->source = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\MarketingListSourceInterface')
            ->getMock();
        $this->handler = new DecisionHandler();
    }

    public function testAllowExtendedMergeVar()
    {
        $sourceCode = 'dummy_code';
        $this->source->expects($this->exactly(3))->method('getCode')->will($this->returnValue($sourceCode));

        $this->handler->allowExtendedMergeVar($this->source);

        $this->assertCount(1, $this->handler->getAllowedSources());
        $this->assertContains($sourceCode, $this->handler->getAllowedSources());

        $this->handler->allowExtendedMergeVar($this->source);

        $this->assertCount(1, $this->handler->getAllowedSources());
    }

    public function testGetAllowedSources()
    {
        $this->assertTrue(is_array($this->handler->getAllowedSources()));
        $this->assertEmpty($this->handler->getAllowedSources());

        $sourceCode = 'dummy_code';
        $this->source->expects($this->exactly(2))->method('getCode')->will($this->returnValue($sourceCode));

        $this->handler->allowExtendedMergeVar($this->source);

        $this->assertCount(1, $this->handler->getAllowedSources());
        $this->assertContains($sourceCode, $this->handler->getAllowedSources());
    }

    public function testIsAllow()
    {
        $sourceCode = 'dummy_code';
        $this->source->expects($this->exactly(2))->method('getCode')->will($this->returnValue($sourceCode));
        $marketingList = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->getMock();
        $marketingList->expects($this->once())->method('getSource')->will($this->returnValue($sourceCode));

        $this->handler->allowExtendedMergeVar($this->source);

        $this->assertTrue($this->handler->isAllow($marketingList));
    }

    public function testIsAllowWhenSourceIsNotInTheList()
    {
        $sourceCode = 'dummy_code';
        $this->source->expects($this->exactly(2))->method('getCode')->will($this->returnValue($sourceCode));
        $marketingList = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->getMock();
        $marketingList->expects($this->once())->method('getSource')->will($this->returnValue('another_dummy_code'));

        $this->handler->allowExtendedMergeVar($this->source);

        $this->assertFalse($this->handler->isAllow($marketingList));
    }
}
