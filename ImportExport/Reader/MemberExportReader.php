<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\SubscribersListRepository;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberExportListIterator;

class MemberExportReader extends IteratorBasedReader
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
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

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
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->getSourceIterator()) {
            /** @var SubscribersListRepository $repo */
            $repo = $this->doctrineHelper
                ->getEntityManager($this->subscribersListClassName)
                ->getRepository($this->subscribersListClassName);

            $subscribersLists = $repo->getAllSubscribersListIterator();

            $iterator = new MemberExportListIterator($subscribersLists);
            $iterator->setMemberClassName($this->memberClassName);
            $iterator->setDoctrineHelper($this->doctrineHelper);

            $this->setSourceIterator($iterator);
        }
    }
}
