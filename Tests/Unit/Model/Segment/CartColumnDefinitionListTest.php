<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\Segment;

use OroCRM\Bundle\MailChimpBundle\Model\Segment\CartColumnDefinitionList;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionListInterface;

class CartColumnDefinitionListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CartColumnDefinitionList
     */
    private $cartColumnDefinitionList;

    /**
     * @var ColumnDefinitionListInterface
     */
    private $columnDefinitionList;

    protected function setUp()
    {
        $this->columnDefinitionList = $this
            ->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionListInterface')
            ->getMock();
        $columns = array(
            array(
                'name' => 'fname',
                'label' => 'First Name'
            )
        );
        $this->columnDefinitionList->expects($this->once())->method('getColumns')
            ->will($this->returnValue($columns));
        $this->cartColumnDefinitionList = new CartColumnDefinitionList($this->columnDefinitionList);
    }

    public function testGetColumns()
    {
        $columns = $this->cartColumnDefinitionList->getColumns();

        $this->assertNotEmpty($columns);
        $this->assertCount(4, $columns);
        $column1 = reset($columns);
        $column2 = next($columns);
        $this->assertThat($column1['name'], $this->equalTo('fname'));
        $this->assertThat($column1['label'], $this->equalTo('First Name'));
        $this->assertThat($column2['name'], $this->equalTo('item_1'));
        $this->assertThat($column2['label'], $this->equalTo('First Cart Item'));
    }
}
