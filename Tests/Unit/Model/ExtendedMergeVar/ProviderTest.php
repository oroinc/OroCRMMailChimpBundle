<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\ExtendedMergeVar;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\Provider;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionListFactory;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionListInterface;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ColumnDefinitionListFactory
     */
    protected $columnDefinitionListFactory;

    /**
     * @var MarketingList
     */
    protected $marketingList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ColumnDefinitionListInterface
     */
    protected $columnDefinitionList;

    protected function setUp()
    {
        $this->columnDefinitionListFactory = $this
            ->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionListFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->columnDefinitionList = $this
            ->getMock('OroCRM\Bundle\MailChimpBundle\Model\Segment\ColumnDefinitionListInterface');
        $this->marketingList = new MarketingList();
        $this->columnDefinitionListFactory
            ->expects($this->any())
            ->method('create')
            ->with($this->marketingList)
            ->will($this->returnValue($this->columnDefinitionList));
        $this->provider = new Provider($this->columnDefinitionListFactory);
    }

    protected function tearDown()
    {
        unset($this->provider);
        unset($this->columnDefinitionListFactory);
        unset($this->columnDefinitionList);
        unset($this->marketingList);
    }

    public function testProvideExtendedMergeVarsWithOutExternalProviders()
    {
        $columns = $this->getSegmentExtendedMergeVars();

        $this->columnDefinitionList->expects($this->once())->method('getColumns')
            ->will($this->returnValue($columns));

        $extendedMergeVars = $this->provider->provideExtendedMergeVars($this->marketingList);

        $this->assertEquals($columns, $extendedMergeVars);
    }

    /**
     * @dataProvider extendedMergeVarsDataProvider
     * @param array $segmentExtendedMergeVars
     * @param array $externalProviderMergeVars
     * @param array $inheritedProviderMergeVars
     */
    public function testProvideExtendedMergeVarsWithExternalProviders(
        array $segmentExtendedMergeVars,
        array $externalProviderMergeVars,
        array $inheritedProviderMergeVars
    ) {
        $this->columnDefinitionList->expects($this->once())->method('getColumns')
            ->will($this->returnValue($segmentExtendedMergeVars));

        /** @var \PHPUnit_Framework_MockObject_MockObject|ProviderInterface $externalProvider */
        $externalProvider = $this
            ->getMock('OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface');
        $externalProvider->expects($this->once())->method('provideExtendedMergeVars')
            ->will($this->returnValue($externalProviderMergeVars));

        /** @var \PHPUnit_Framework_MockObject_MockObject|ProviderInterface $inheritedExternalProvider */
        $inheritedExternalProvider = $this
            ->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface')
            ->setMockClassName('InheritedProvider')
            ->getMock();
        $externalProvider->expects($this->once())->method('provideExtendedMergeVars')
            ->will($this->returnValue($inheritedProviderMergeVars));

        $this->provider->addProvider($externalProvider);
        $this->provider->addProvider($externalProvider);
        $this->provider->addProvider($inheritedExternalProvider);

        $actual = $this->provider->provideExtendedMergeVars($this->marketingList);

        $expected = array_merge(
            $segmentExtendedMergeVars,
            $externalProviderMergeVars
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function extendedMergeVarsDataProvider()
    {
        return [
            [
                $this->getSegmentExtendedMergeVars(),
                [
                    [
                        'name' => 'e_dummy_name',
                        'label' => 'e_dummy_label'
                    ]
                ],
                [
                    [
                        'name' => 'inherited_name',
                        'label' => 'inherited_label'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getSegmentExtendedMergeVars()
    {
        return [
            [
                'name' => 'dummy_name',
                'label' => 'dummy_label'
            ]
        ];
    }
}
