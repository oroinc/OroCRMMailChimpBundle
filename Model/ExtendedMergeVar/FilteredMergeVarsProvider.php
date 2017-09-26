<?php

namespace Oro\Bundle\MailChimpBundle\Model\ExtendedMergeVar;

use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class FilteredMergeVarsProvider implements ProviderInterface
{
    /**
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @var DQLNameFormatter
     */
    protected $nameFormatter;

    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    public function __construct(
        ProviderInterface $provider,
        DQLNameFormatter $nameFormatter,
        ContactInformationFieldsProvider $contactInformationFieldsProvider
    ) {
        $this->provider = $provider;
        $this->nameFormatter = $nameFormatter;
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(MarketingList $marketingList)
    {
        return $this->provider->isApplicable($marketingList);
    }

    /**
     * {@inheritdoc}
     */
    public function provideExtendedMergeVars(MarketingList $marketingList)
    {
        $excludedFields = [];

        $contactInformationFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );
        if ($contactInformationFields) {
            $excludedFields[] = reset($contactInformationFields);   // exclude main email field
        }

        $nameFields = $this->nameFormatter->getSuggestedFieldNames($marketingList->getEntity());
        if (!empty($nameFields['first_name'])) {
            $excludedFields[] = $nameFields['first_name'];
        }
        if (!empty($nameFields['last_name'])) {
            $excludedFields[] = $nameFields['last_name'];
        }

        $filteredVars = [];
        foreach ($this->provider->provideExtendedMergeVars($marketingList) as $var) {
            if (!in_array($var['name'], $excludedFields)) {
                $filteredVars[] = $var;
            }
        }

        return $filteredVars;
    }
}
