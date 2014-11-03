<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\StaticSegment;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentsMemberStateManager
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $staticSegmentMember;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param string $staticSegmentMember
     */
    public function __construct(DoctrineHelper $doctrineHelper, $staticSegmentMember)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->staticSegmentMember = $staticSegmentMember;
    }

    /**
     * @param StaticSegment $staticSegment
     */
    public function drop(StaticSegment $staticSegment)
    {
        $qb = $this->doctrineHelper
            ->getEntityManager($this->staticSegmentMember)
            ->createQueryBuilder('smmb');

        $qb
            ->delete($this->staticSegmentMember, 'smmb')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('smmb.staticSegment', ':staticSegment'),
                    $qb->expr()->eq('smmb.state', ':state')
                )
            )
            ->setParameters(
                [
                    'staticSegment' => $staticSegment->getId(),
                    'state' => StaticSegmentMember::STATE_DROP,
                ]
            )
            ->getQuery()
            ->execute();
    }
}

