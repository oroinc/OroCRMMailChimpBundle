<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\SubscribersListRepository;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberExportListIterator;

class MemberExportReader extends AbstractSubscribersListReader
{
    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * @param string $memberClassName
     */
    public function setMemberClassName($memberClassName)
    {
        $this->memberClassName = $memberClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->getSourceIterator()) {
            $iterator = new MemberExportListIterator($this->getSubscribersListIterator());
            $iterator->setMemberClassName($this->memberClassName);
            $iterator->setDoctrineHelper($this->doctrineHelper);

            $this->setSourceIterator($iterator);
        }
    }

    /**
     * @return BufferedQueryResultIterator
     */
    protected function getSubscribersListIterator()
    {
        /** @var SubscribersListRepository $repository */
        $repository = $this->doctrineHelper
            ->getEntityManager($this->subscribersListClassName)
            ->getRepository($this->subscribersListClassName);

        return $repository->getAllSubscribersListIterator();
    }
}
