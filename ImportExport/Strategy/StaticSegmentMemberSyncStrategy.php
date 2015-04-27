<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy as BaseStrategy;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentMemberSyncStrategy extends BaseStrategy
{
    /**
     * @param StaticSegmentMember $entity
     * @return StaticSegmentMember|null
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        $entity = $this->beforeProcessEntity($entity);
        $entity = $this->processEntity($entity);
        $entity = $this->afterProcessEntity($entity);

        return $entity;
    }

    /**
     * @param StaticSegmentMember $member
     * @return StaticSegmentMember
     */
    protected function processEntity(StaticSegmentMember $member)
    {
        $member->setStaticSegment(
            $this->databaseHelper->getEntityReference($member->getStaticSegment())
        );
        $member->setMember(
            $this->databaseHelper->getEntityReference($member->getMember())
        );

        return $member;
    }
}
