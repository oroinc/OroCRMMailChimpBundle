<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Reader\SubordinateReaderInterface;

class MemberExportListIterator extends AbstractSubscribersListIterator implements SubordinateReaderInterface
{
    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * @param \Iterator $mainIterator
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        \Iterator $mainIterator,
        DoctrineHelper $doctrineHelper
    ) {
        $this->mainIterator = $mainIterator;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @return bool
     */
    public function writeRequired()
    {
        if (!$this->subordinateIterator) {
            return false;
        }

        return !$this->subordinateIterator->valid();
    }

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
    protected function createSubordinateIterator($subscribersList)
    {
        parent::assertSubscribersList($subscribersList);

        if (!$this->memberClassName) {
            throw new \InvalidArgumentException('Member id must be provided');
        }

        $qb = $this->doctrineHelper
            ->getEntityManager($this->memberClassName)
            ->getRepository($this->memberClassName)
            ->createQueryBuilder('mmb');

        $qb
            ->select('mmb')
            ->join('mmb.subscribersList', 'subscribersList')
            ->andWhere($qb->expr()->eq('mmb.status', ':status'))
            ->andWhere($qb->expr()->eq('subscribersList.originId', ':originId'))
            ->setParameters(
                [
                    'status' => Member::STATUS_EXPORT,
                    'originId' => $subscribersList->getOriginId()
                ]
            )
            ->addOrderBy('subscribersList.id');

        return new BufferedQueryResultIterator($qb);
    }
}
