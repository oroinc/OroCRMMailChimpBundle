<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\SubscribersListRepository;

abstract class AbstractSubscribersListReader extends AbstractIteratorBasedReader
{
    /**
     * @var string
     */
    protected $subscribersListClassName;

    /**
     * @param string $subscribersListClassName
     */
    public function setSubscribersListClassName($subscribersListClassName)
    {
        $this->subscribersListClassName = $subscribersListClassName;
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
