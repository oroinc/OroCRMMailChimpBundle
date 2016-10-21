<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Strategy;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy as BaseStrategy;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MailChimpBundle\Entity\Member;

class MemberExportStrategy extends BaseStrategy implements LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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

        if ($this->logger) {
            $this->logger->notice(sprintf('Exporting MailChimp Member [id=%s]', $entity->getId()));
        }

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
