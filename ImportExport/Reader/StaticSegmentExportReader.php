<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentExportReader extends IteratorBasedReader
{
    /**
     * @var string
     */
    protected $staticSegmentMemberClassName;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;


    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param string $staticSegmentMemberClassName
     */
    public function setStaticSegmentMemberClassName($staticSegmentMemberClassName)
    {
        $this->staticSegmentMemberClassName = $staticSegmentMemberClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->getSourceIterator()) {
            $qb = $this->doctrineHelper
                ->getEntityManager($this->staticSegmentMemberClassName)
                ->getRepository($this->staticSegmentMemberClassName)
                ->createQueryBuilder('smmb');

            $qb
                ->select('smmb')
                ->join('smmb.member', 'mmb')
                ->where($qb->expr()->neq('smmb.state', ':state'))
                ->setParameter('state', StaticSegmentMember::STATE_SYNCED);

            $iterator = new BufferedQueryResultIterator($qb);

            $this->setSourceIterator($iterator);
        }
    }
}
