<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Model\ExtendedMergeVar;

use Oro\Bundle\MailChimpBundle\Model\ExtendedMergeVar\Provider;
use Oro\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @var MarketingList
     */
    protected $marketingList;

    protected function setUp()
    {
        $this->marketingList = new MarketingList();
        $this->provider = new Provider();
    }

    protected function tearDown()
    {
        unset($this->provider, $this->marketingList);
    }

    public function testProvideExtendedMergeVarsWithOutExternalProviders()
    {
        $extendedMergeVars = $this->provider->provideExtendedMergeVars($this->marketingList);
        $this->assertEquals([], $extendedMergeVars);
    }

    /**
     * @dataProvider extendedMergeVarsDataProvider
     * @param array $externalProviderMergeVars
     * @param array $inheritedProviderMergeVars
     */
    public function testProvideExtendedMergeVarsWithExternalProviders(
        array $externalProviderMergeVars,
        array $inheritedProviderMergeVars
    ) {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProviderInterface $externalProvider */
        $externalProvider = $this
            ->createMock('Oro\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface');
        $externalProvider->expects($this->once())->method('provideExtendedMergeVars')
            ->will($this->returnValue($externalProviderMergeVars));

        /** @var \PHPUnit_Framework_MockObject_MockObject|ProviderInterface $inheritedExternalProvider */
        $inheritedExternalProvider = $this
            ->getMockBuilder('Oro\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface')
            ->setMockClassName('InheritedProvider')
            ->getMock();
        $externalProvider->expects($this->once())->method('provideExtendedMergeVars')
            ->will($this->returnValue($inheritedProviderMergeVars));

        $this->provider->addProvider($externalProvider);
        $this->provider->addProvider($externalProvider);
        $this->provider->addProvider($inheritedExternalProvider);

        $actual = $this->provider->provideExtendedMergeVars($this->marketingList);

        $this->assertEquals($externalProviderMergeVars, $actual);
    }

    /**
     * @return array
     */
    public function extendedMergeVarsDataProvider()
    {
        return [
            [
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
