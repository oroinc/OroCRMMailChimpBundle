<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Model\ExtendedMergeVar;

use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\MailChimpBundle\Model\ExtendedMergeVar\FilteredMergeVarsProvider;
use Oro\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class FilterMergeVarsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;

    /**
     * @var DQLNameFormatter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $nameFormatter;

    /**
     * @var ContactInformationFieldsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var FilteredMergeVarsProvider
     */
    protected $filterProvider;

    public function setUp()
    {
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->nameFormatter = $this->createMock(DQLNameFormatter::class);
        $this->contactInformationFieldsProvider = $this->createMock(ContactInformationFieldsProvider::class);

        $this->filterProvider = new FilteredMergeVarsProvider(
            $this->provider,
            $this->nameFormatter,
            $this->contactInformationFieldsProvider
        );
    }

    public function testIsApplicable()
    {
        $firstMarketingList = new MarketingList();
        $secondMarketingList = new MarketingList();

        $this->provider->expects($this->atLeastOnce())
            ->method('isApplicable')
            ->will($this->returnValueMap([
                [$firstMarketingList, true],
                [$secondMarketingList, false]
            ]));

        $this->assertTrue($this->filterProvider->isApplicable($firstMarketingList));
        $this->assertFalse($this->filterProvider->isApplicable($secondMarketingList));
    }

    public function testProvideExtendedMergeVars()
    {
        $entityClass = \stdClass::class;
        $marketingList = new MarketingList();
        $marketingList->setEntity($entityClass);

        $this->contactInformationFieldsProvider->expects($this->once())
            ->method('getMarketingListTypedFields')
            ->with($marketingList, ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL)
            ->willReturn(['primaryEmail', 'secondaryEmail']);

        $this->nameFormatter->expects($this->once())
            ->method('getSuggestedFieldNames')
            ->with($entityClass)
            ->willReturn(['first_name' => 'firstName', 'middle_name' => 'middleName', 'last_name' => 'lastName']);

        $this->provider->expects($this->once())
            ->method('provideExtendedMergeVars')
            ->with($marketingList)
            ->willReturn([
                ['name' => 'primaryEmail'],
                ['name' => 'secondaryEmail'],
                ['name' => 'firstName'],
                ['name' => 'middleName'],
                ['name' => 'lastName'],
                ['name' => 'gender'],
            ]);

        $this->assertEquals([
            ['name' => 'secondaryEmail'],
            ['name' => 'middleName'],
            ['name' => 'gender'],
        ], $this->filterProvider->provideExtendedMergeVars($marketingList));
    }
}
