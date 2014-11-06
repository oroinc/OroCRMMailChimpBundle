<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\SubscribersListRepository;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberExportListIterator;

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
        if (!$this->memberClassName) {
            throw new InvalidConfigurationException('Member class name must be provided');
        }

        if (!$this->getSourceIterator()) {
            $iterator = new MemberExportListIterator($this->getSubscribersListIterator(), $this->doctrineHelper);
            $iterator->setMemberClassName($this->memberClassName);

            $this->setSourceIterator($iterator);
        }
    }

    /**
     * @return BufferedQueryResultIterator
     */
    protected function getSubscribersListIterator()
    {
        if (!$this->subscribersListClassName) {
            throw new InvalidConfigurationException('SubscribersList class name must be provided');
        }

        /** @var SubscribersListRepository $repository */
        $repository = $this->doctrineHelper
            ->getEntityManager($this->subscribersListClassName)
            ->getRepository($this->subscribersListClassName);

        return $repository->getAllSubscribersListIterator();
    }
}
