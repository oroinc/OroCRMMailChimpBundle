<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MailChimpBundle\ImportExport\Reader\SubordinateReaderInterface;

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
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('mmb.status', ':status'),
                    $qb->expr()->eq('mmb.subscribersList', ':subscribersList')
                )
            )
            ->setParameters(
                [
                    'status' => Member::STATUS_EXPORT,
                    'subscribersList' => $subscribersList
                ]
            )
            ->addOrderBy('mmb.id');

        $bufferedIterator = new BufferedIdentityQueryResultIterator($qb);

        return $bufferedIterator;
    }
}
