<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MailChimpBundle\Entity\Repository\SubscribersListRepository;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberExportListIterator;

class MemberExportReader extends AbstractIteratorBasedReader
{
    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * @var string
     */
    protected $subscribersListClassName;

    /**
     * @param string $memberClassName
     */
    public function setMemberClassName($memberClassName)
    {
        $this->memberClassName = $memberClassName;
    }

    /**
     * @param string $subscribersListClassName
     */
    public function setSubscribersListClassName($subscribersListClassName)
    {
        $this->subscribersListClassName = $subscribersListClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);

        if (!$this->memberClassName) {
            throw new InvalidConfigurationException('Member class name must be provided');
        }

        if (!$this->getSourceIterator()) {
            /** @var Channel $channel */
            $channel = $this->doctrineHelper->getEntityReference(
                $this->channelClassName,
                $context->getOption('channel')
            );

            $iterator = new MemberExportListIterator(
                $this->getSubscribersListIterator($channel),
                $this->doctrineHelper
            );
            $iterator->setMemberClassName($this->memberClassName);

            $this->setSourceIterator($iterator);
        }
    }

    /**
     * @param Channel $channel
     *
     * @return \Iterator
     */
    protected function getSubscribersListIterator(Channel $channel)
    {
        if (!$this->subscribersListClassName) {
            throw new InvalidConfigurationException('SubscribersList class name must be provided');
        }

        /** @var SubscribersListRepository $repository */
        $repository = $this->doctrineHelper
            ->getEntityManager($this->subscribersListClassName)
            ->getRepository($this->subscribersListClassName);

        return $repository->getUsedSubscribersListIterator($channel);
    }
}
