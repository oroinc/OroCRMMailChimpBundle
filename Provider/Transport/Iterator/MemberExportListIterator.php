<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Reader\SubordinateReaderInterface;

class MemberExportListIterator extends AbstractSubscribersListIterator implements SubordinateReaderInterface
{
    /**
     * @var string
     */
    protected $memberClassName;

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
            )
            ->addOrderBy('subscribersList.id');

        return new BufferedQueryResultIterator($qb);
    }
}
