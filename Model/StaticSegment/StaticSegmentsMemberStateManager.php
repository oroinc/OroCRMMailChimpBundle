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
     * @var string
     */
    protected $mailChimpMember;

    /**
     * @var string
     */
    protected $extMergeVar;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param $staticSegmentMember
     * @param $mailChimpMember
     * @param $extMergeVar
     */
    public function __construct(DoctrineHelper $doctrineHelper, $staticSegmentMember, $mailChimpMember, $extMergeVar)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->staticSegmentMember = $staticSegmentMember;
        $this->mailChimpMember = $mailChimpMember;
        $this->extMergeVar = $extMergeVar;
    }

    /**
     * @param StaticSegment $staticSegment
     */
    public function handleMembers(StaticSegment $staticSegment)
    {
        $staticSegmentRep = $this->doctrineHelper->getEntityRepository($this->staticSegmentMember);

        $deletedMembers = $staticSegmentRep->createQueryBuilder('smmb')
            ->select('IDENTITY(smmb.member) AS memberId')
            ->where('smmb.staticSegment = :staticSegment AND smmb.state = :state')
            ->setParameter('staticSegment', $staticSegment->getId())
            ->setParameter('state', StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE)
            ->getQuery()
            ->getArrayResult();

        $this->handleDroppedMembers($staticSegment);

        if (!empty($deletedMembers)) {
            $deletedMembersIds = array_map('current', $deletedMembers);
            $deletedMembersIds = implode($deletedMembersIds, ',');
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
            ->andWhere('smmb.staticSegment = :staticSegment')
            ->andWhere('smmb.state = :state_drop OR smmb.state = :state_delete')
            ->setParameters(
                [
                    'staticSegment' => $staticSegment->getId(),
                    'state_drop' => StaticSegmentMember::STATE_DROP,
                    'state_delete' => StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE,
                ]
            )
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $subscribersList
     * @param string $deletedMembersIds
     */
    private function deleteMailChimpMembers($deletedMembersIds, $subscribersList)
    {
        $qb = $this->doctrineHelper
            ->getEntityManager($this->mailChimpMember)
            ->createQueryBuilder('mmb');

        $qb
            ->delete($this->mailChimpMember, 'mmb')
            ->andWhere('mmb.id IN (:memberIds)')
            ->andWhere('mmb.subscribersList = :subscribersList')
            ->setParameters(
                [
                    'memberIds' => $deletedMembersIds,
                    'subscribersList' => $subscribersList,
                ]
            )
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $staticSegmentId
     * @param string $deletedMembersIds
     */
    private function deleteMailChimpMembersExtendedVars($deletedMembersIds, $staticSegmentId)
    {
        $qb = $this->doctrineHelper
            ->getEntityManager($this->extMergeVar)
            ->createQueryBuilder('evmmb');

        $qb
            ->delete($this->extMergeVar, 'evmmb')
            ->andWhere('evmmb.member IN (:memberIds)')
            ->andWhere('evmmb.staticSegment = :staticSegment')
            ->setParameters(
                [
                    'memberIds' => $deletedMembersIds,
                    'staticSegment' => $staticSegmentId
                ]
            )
            ->getQuery()
            ->execute();
    }
}
