<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy as BasicImportStrategy;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;

class UnsentActivityStrategy extends BasicImportStrategy
{
    /**
     * @param MemberActivity $entity
     * @return MemberActivity
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        $entity = $this->beforeProcessEntity($entity);
        $this->updateRelations($entity);
        $entity = $this->processEntity($entity);
        $entity = $this->afterProcessEntity($entity);

        return $entity;
    }

    /**
     * @param MemberActivity $entity
     * @return MemberActivity
     */
    protected function processEntity($entity)
    {
        $entity
            ->setAction(MemberActivity::ACTIVITY_SENT)
            ->setActivityTime($entity->getCampaign()->getSendTime());

        return $entity;
    }

    /**
     * @param MemberActivity $entity
     * @return MemberActivity
     */
    protected function updateRelations($entity)
    {
        $entity->setCampaign(
            $this->findExistingEntity($entity->getCampaign())
        );
        $entity->setChannel(
            $this->databaseHelper->getEntityReference($entity->getChannel())
        );
        $entity->setMember(
            $this->databaseHelper->getEntityReference($entity->getMember())
        );
    }
}
