<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\StaticSegment;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

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
     * @var string
     */
    protected $mailChimpMemberСlassName;

    /**
     * @var string
     */
    protected $extMergeVarClassName;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param $staticSegmentMember
     * @param $mailChimpMemberСlassName
     * @param $extMergeVarClassName
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        $staticSegmentMember,
        $mailChimpMemberСlassName,
        $extMergeVarClassName
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->staticSegmentMember = $staticSegmentMember;
        $this->mailChimpMemberСlassName = $mailChimpMemberСlassName;
        $this->extMergeVarClassName = $extMergeVarClassName;
    }

    /**
     * @param StaticSegment $staticSegment
     */
    public function handleMembers(StaticSegment $staticSegment)
    {
        $staticSegmentRep = $this->doctrineHelper->getEntityRepository($this->staticSegmentMember);

        $qb = $staticSegmentRep->createQueryBuilder('smmb');

        $deletedMembers = $qb
            ->select('IDENTITY(smmb.member) AS memberId')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('smmb.staticSegment', $staticSegment->getId()),
                    $qb->expr()->eq('smmb.state', ':stateUnsDel')
                )
            )
            ->setParameter('stateUnsDel', StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE)
            ->getQuery()
            ->getArrayResult();

        $this->handleDroppedMembers($staticSegment);

        if ($deletedMembers) {
            $deletedMembersIds = array_map('current', $deletedMembers);
            $this->deleteMailChimpMembers($deletedMembersIds, $staticSegment->getSubscribersList());
            $this->deleteMailChimpMembersExtendedVars($deletedMembersIds, $staticSegment->getId());
        }
    }

    /**
     * @param StaticSegment $staticSegment
     */
    protected function handleDroppedMembers(StaticSegment $staticSegment)
    {
        $qb = $this->doctrineHelper
            ->getEntityManager($this->staticSegmentMember)
            ->createQueryBuilder('smmb');

        $qb
            ->delete($this->staticSegmentMember, 'smmb')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('smmb.staticSegment', $staticSegment->getId()),
                    $qb->expr()->in('smmb.state', ':states')
                )
            )
            ->setParameter('states', [StaticSegmentMember::STATE_DROP, StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE])
            ->getQuery()
            ->execute();
    }

    /**
     * @param array $deletedMembersIds
     * @param SubscribersList $subscribersList
     */
    protected function deleteMailChimpMembers(array $deletedMembersIds, SubscribersList $subscribersList)
    {
        $qb = $this->doctrineHelper
            ->getEntityManager($this->mailChimpMemberСlassName)
            ->createQueryBuilder('mmb');

        $qb
            ->delete($this->mailChimpMemberСlassName, 'mmb')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->in('mmb.id', ':deletedMembersIds'),
                    $qb->expr()->eq('mmb.subscribersList', $subscribersList->getId())
                )
            )
            ->setParameter('deletedMembersIds', $deletedMembersIds)
            ->getQuery()
            ->execute();
    }

    /**
     * @param array $deletedMembersIds
     * @param integer $staticSegmentId
     */
    protected function deleteMailChimpMembersExtendedVars(array $deletedMembersIds, $staticSegmentId)
    {
        $qb = $this->doctrineHelper
            ->getEntityManager($this->extMergeVarClassName)
            ->createQueryBuilder('evmmb');

        $qb
            ->delete($this->extMergeVarClassName, 'evmmb')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->in('evmmb.member', ':deletedMembersIds'),
                    $qb->expr()->eq('evmmb.staticSegment', $staticSegmentId)
                )
            )
            ->setParameter('deletedMembersIds', $deletedMembersIds)
            ->getQuery()
            ->execute();
    }
}
