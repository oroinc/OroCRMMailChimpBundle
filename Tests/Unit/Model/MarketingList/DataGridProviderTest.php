<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\MarketingList;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;

use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListProvider;
use OroCRM\Bundle\MailChimpBundle\Model\MarketingList\DataGridProvider;

class DataGridProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Manager
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
        /** @var \PHPUnit_Framework_MockObject_MockObject|MarketingList $marketingList */
        $marketingList = $this
            ->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->any())->method('getId')->will($this->returnValue(1));
        $marketingList->expects($this->any())->method('isManual')
            ->will($this->returnValue($type === MarketingListType::TYPE_MANUAL));

        $config = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid = $this->getMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');

        $dataGrid->expects($this->once())->method('getConfig')->will($this->returnValue($config));

        $this->dataGridManager->expects($this->atLeastOnce())->method('getDatagrid')
            ->with(
                ConfigurationProvider::GRID_PREFIX . $marketingList->getId(),
                $this->logicalAnd(
                    $this->arrayHasKey('grid-mixin'),
                    $this->callback(function ($other) use ($type) {
                        if ($type === MarketingListType::TYPE_MANUAL) {
                            $mixin = MarketingListProvider::MANUAL_RESULT_ENTITIES_MIXIN;
                        } else {
                            $mixin = MarketingListProvider::RESULT_ENTITIES_MIXIN;
                        }
                        return $other['grid-mixin'] === $mixin;
                    })
                )
            )
            ->will($this->returnValue($dataGrid));

        $dataGridConfiguration = $this->dataGridProvider->getDataGridConfiguration($marketingList);

        $this->assertEquals($config, $dataGridConfiguration);
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
