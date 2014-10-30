<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;

class MemberExportReader extends IteratorBasedReader
{
    /**
     * @var string
     */
    protected $memberClassName;

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
        /** @todo: add iterator based on AbstractSubordinateIterator */

        if (!$this->getSourceIterator()) {
            $qb = $this->doctrineHelper
                ->getEntityManager($this->memberClassName)
                ->getRepository($this->memberClassName)
                ->createQueryBuilder('mmb');

            $qb
                ->select('mmb')
                ->join('mmb.subscribersList', 'subscribersList')
                ->where($qb->expr()->eq('mmb.status', ':status'))
                ->setParameter('status', Member::STATUS_UNSUBSCRIBED)
                ->orderBy('subscribersList.originId');

            $iterator = new BufferedQueryResultIterator($qb);

            $this->setSourceIterator($iterator);
        }
    }
}
