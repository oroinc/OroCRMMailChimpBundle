<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;

class CampaignImportStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @var DefaultOwnerHelper
     */
    protected $ownerHelper;

    /**
     * @param DefaultOwnerHelper $ownerHelper
     */
    public function setOwnerHelper(DefaultOwnerHelper $ownerHelper)
    {
        $this->ownerHelper = $ownerHelper;
    }

    /**
     * @param Campaign $entity
     * @return Campaign
     */
    protected function afterProcessEntity($entity)
    {
        $this->ownerHelper->populateChannelOwner($entity->getEmailCampaign(), $entity->getChannel());

        return $entity;
    }
}
