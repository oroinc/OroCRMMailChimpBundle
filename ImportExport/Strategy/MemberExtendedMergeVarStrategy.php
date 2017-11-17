<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\MailChimpBundle\Entity\MemberExtendedMergeVar;

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

        // Set state to synced if merge vars are already synced, e.g. during execution of
        // oro_mailchimp.importexport.writer.member
        if ($entity->isAddState()) {
            $memberMergeVarValues = $entity->getMember()->getMergeVarValues();
            if (!array_diff($entity->getMergeVarValues(), $memberMergeVarValues)) {
                $entity->setState(MemberExtendedMergeVar::STATE_SYNCED);
            }
        }

        return parent::afterProcessEntity($entity);
    }
}
