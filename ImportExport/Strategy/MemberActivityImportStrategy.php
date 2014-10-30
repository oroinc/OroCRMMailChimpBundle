<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

use Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy as BasicImportStrategy;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;

class MemberActivityImportStrategy extends BasicImportStrategy implements LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DefaultOwnerHelper
     */
    protected $ownerHelper;

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param DefaultOwnerHelper $ownerHelper
     */
    public function setOwnerHelper(DefaultOwnerHelper $ownerHelper)
    {
        $this->ownerHelper = $ownerHelper;
    }

    /**
     * @param MemberActivity $entity
     * @return MemberActivity
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        $entity = $this->beforeProcessEntity($entity);
        $entity = $this->processEntity($entity, $this->context->getValue('itemData'));
        $entity = $this->afterProcessEntity($entity);

        $this->context->incrementAddCount();

        return $entity;
    }

    /**
     * @param MemberActivity $entity
     * @param mixed $itemData
     * @return MemberActivity
     */
    protected function processEntity($entity, $itemData = null)
    {
        if ($this->logger) {
            $this->logger->info(
                'Adding new MailChimp Member [email=' . $entity->getEmail() . ', action=' . $entity->getAction() . ']'
            );
        }

        return $entity;
    }
}
