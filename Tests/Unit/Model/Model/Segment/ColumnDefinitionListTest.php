<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\Model\Segment;

use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionList;

class ColumnDefinitionListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ColumnDefinitionList
     */
    private $list;

    /**
     * @var Segment
     */
    private $segment;

    protected function setUp()
    {
        $this->segment = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')->getMock();
        $this->list = new ColumnDefinitionList($this->segment);
    }

    public function testGetColumnsWhenJsonRepresentationIsIncorrect()
    {
        /** @var Segment $segment */
        $segment = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')->getMock();
        $segment->expects($this->once())->method('getDefinition')
            ->will($this->returnValue('incorrect_definition'));

        $list = new ColumnDefinitionList($segment);

        $this->assertEmpty($list->getColumns());
    }

    public function testGetColumnsWhenDefinitionHasNoColumns()
    {
        /** @var Segment $segment */
        $segment = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')->getMock();
        $definition = json_encode(array('filters' => array()));
        $segment->expects($this->once())->method('getDefinition')->will($this->returnValue($definition));

        $list = new ColumnDefinitionList($segment);

        $this->assertEmpty($list->getColumns());
    }

    public function testGetColumnsWhenColumnDefinitionIsIncorrect()
    {
        /** @var Segment $segment */
        $segment = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')->getMock();

        $definition = json_encode(array(
            'columns' => array(
                array('name' => 'email', 'func' => null),
                array('name' => 'total', 'label' => 'Total', 'func' => null)
            ),
            'filters' => array()
        ));

        $segment->expects($this->once())->method('getDefinition')->will($this->returnValue($definition));

        $list = new ColumnDefinitionList($segment);
        $columns = $list->getColumns();

        $this->assertCount(1, $columns);
        $column = current($columns);
        $this->assertThat($column['name'], $this->equalTo('total'));
        $this->assertThat($column['label'], $this->equalTo('Total'));
    }

    public function testGetColumns()
    {
        /** @var Segment $segment */
        $segment = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')->getMock();

        $definition = json_encode($this->getCorrectSegmentDefinition());

        $segment->expects($this->once())->method('getDefinition')->will($this->returnValue($definition));

        $list = new ColumnDefinitionList($segment);

        $columns = $list->getColumns();

        $this->assertCount(2, $columns);

        $column1 = reset($columns);
        $column2 = next($columns);

        $this->assertArrayHasKey('name', $column1);
        $this->assertArrayHasKey('label', $column2);
        $this->assertThat($column1['name'], $this->equalTo('email'));
        $this->assertThat($column1['label'], $this->equalTo('Email'));
        $this->assertThat($column2['name'], $this->equalTo('total'));
        $this->assertThat($column2['label'], $this->equalTo('Total'));
    }

    public function testGetIterator()
    {
        /** @var Segment $segment */
        $segment = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')->getMock();

        $definition = json_encode($this->getCorrectSegmentDefinition());
        $segment->expects($this->once())->method('getDefinition')->will($this->returnValue($definition));

        $list = new ColumnDefinitionList($segment);

        $this->assertInstanceOf('\Iterator', $list->getIterator());

        foreach ($list as $each) {
            $this->assertArrayHasKey('name', $each);
            $this->assertArrayHasKey('label', $each);
            $this->assertThat(
                $each['name'],
                $this->logicalOr(
                    $this->equalTo('email'),
                    $this->equalTo('total')
                )
            );
            $this->assertThat(
                $each['label'],
                $this->logicalOr(
                    $this->equalTo('Email'),
                    $this->equalTo('Total')
                )
            );
        }
    }

    private function getCorrectSegmentDefinition()
    {
        return array(
            'columns' => array(
                array('name' => 'email', 'label' => 'Email', 'func' => null),
                array('name' => 'total', 'label' => 'Total', 'func' => null)
            ),
            'filters' => array()
        );
    }
}
