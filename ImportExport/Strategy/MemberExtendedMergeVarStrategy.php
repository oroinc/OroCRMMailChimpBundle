<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

class MemberExtendedMergeVarStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        $itemData = $this->context->getValue('itemData');
        $entity->setMergeVarValuesContext($itemData);
        return parent::afterProcessEntity($entity);
    }
}
