<?php

namespace Oro\Bundle\MailChimpBundle\Placeholder;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\MailChimpBundle\Provider\ChannelType;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class ButtonsPlaceholderFilter
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $fieldsProvider;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ContactInformationFieldsProvider $fieldsProvider
     * @param ManagerRegistry $registry
     */
    public function __construct(ContactInformationFieldsProvider $fieldsProvider, ManagerRegistry $registry)
    {
        $this->fieldsProvider = $fieldsProvider;
        $this->registry = $registry;
    }

    /**
     * @param mixed $entity
     * @return bool
     */
    public function isApplicable($entity)
    {
        if ($entity instanceof MarketingList) {
            if (!$this->hasMailChimpIntegration()) {
                return false;
            }

            return (bool)$this->fieldsProvider->getMarketingListTypedFields(
                $entity,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
            );
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function hasMailChimpIntegration()
    {
        return (bool)$this->registry->getRepository('OroIntegrationBundle:Channel')
            ->getConfiguredChannelsForSync(ChannelType::TYPE, true);
    }
}
