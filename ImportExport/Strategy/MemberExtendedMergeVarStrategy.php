<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberExtendedMergeVar;

class MemberExtendedMergeVarStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @param MemberExtendedMergeVar $entity
     *
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        $itemData = $this->context->getValue('itemData');
        $entity->setMergeVarValuesContext($itemData);
        return parent::afterProcessEntity($entity);
    }
}
