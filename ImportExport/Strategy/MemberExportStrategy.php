<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy as BaseStrategy;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;

class MemberExportStrategy extends BaseStrategy
{
    /**
     * @param Member|object $entity
     * @return Member|null
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        /** @var Member $entity */
        $entity = $this->beforeProcessEntity($entity);
        $entity = $this->processEntity($entity);
        $entity = $this->afterProcessEntity($entity);

        return $entity;
    }

    /**
     * @param Member $member
     * @return Member
     */
    protected function processEntity(Member $member)
    {
        $member->setSubscribersList(
            $this->databaseHelper->getEntityReference($member->getSubscribersList())
        );
        /** @var Channel $channel */
        $channel = $this->databaseHelper->getEntityReference($member->getChannel());
        $member->setChannel($channel);

        return $member;
    }
}
