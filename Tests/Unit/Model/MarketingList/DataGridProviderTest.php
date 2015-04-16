<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\MarketingList;

use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;
use OroCRM\Bundle\MailChimpBundle\Model\MarketingList\DataGridProvider;

class DataGridProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataGridManager;

    /**
     * @var DataGridProvider
     */
    protected $dataGridProvider;

    protected function setUp()
    {
        $this->dataGridManager = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataGridProvider = new DataGridProvider($this->dataGridManager);
    }

    protected function tearDown()
    {
        unset($this->dataGridManager);
        unset($this->dataGridProvider);
    }

    /**
     * @dataProvider marketingListTypeDataProvider
     * @param string $type
     */
    public function testGetDataGridColumns($type)
    {
        $expectedColumns = [
            'column1',
            'column2'
        ];

        $marketingList = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->any())->method('getId')->will($this->returnValue(1));
        $marketingList->expects($this->any())->method('isManual')
            ->will($this->returnValue($type === MarketingListType::TYPE_MANUAL));

        $config = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->getMock();

        $dataGrid->expects($this->once())->method('getConfig')->will($this->returnValue($config));

        $this->dataGridManager->expects($this->atLeastOnce())->method('getDatagrid')
            ->with(
                ConfigurationProvider::GRID_PREFIX . $marketingList->getId(),
                $this->logicalAnd(
                    $this->arrayHasKey('grid-mixin'),
                    $this->callback(function($other) use ($type) {
                        if ($type === MarketingListType::TYPE_MANUAL) {
                            $mixin = MarketingListProvider::MANUAL_RESULT_ENTITIES_MIXIN;;
                        } else {
                            $mixin = MarketingListProvider::RESULT_ENTITIES_MIXIN;
                        }
                        return $other['grid-mixin'] === $mixin;
                    })
                )
            )
            ->will($this->returnValue($dataGrid));

        $config->expects($this->once())->method('offsetGet')
            ->with('columns')->will($this->returnValue($expectedColumns));

        $actualColumns = $this->dataGridProvider->getDataGridColumns($marketingList);

        $this->assertEquals($expectedColumns, $actualColumns);
    }

    /**
     * @return array
     */
    public function marketingListTypeDataProvider()
    {
        return [
            [MarketingListType::TYPE_MANUAL],
            [MarketingListType::TYPE_DYNAMIC]
        ];
    }
}
