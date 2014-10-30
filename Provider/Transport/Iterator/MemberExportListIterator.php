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
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @return bool
     */
    public function writeRequired()
    {
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
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($subscribersList)
    {
        parent::assertSubscribersList($subscribersList);

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
                    'status' => Member::STATUS_UNSUBSCRIBED,
                    'originId' => $subscribersList->getOriginId()
                ]
            );

        return new BufferedQueryResultIterator($qb);
    }
}
