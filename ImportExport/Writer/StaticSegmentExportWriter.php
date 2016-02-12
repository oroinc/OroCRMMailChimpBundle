<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Psr\Log\LoggerInterface;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentExportWriter extends AbstractExportWriter
{
    const BATCH_SIZE = 2000;

    /**
     * @var string
     */
    protected $staticSegmentMemberClassName;

    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @param string $staticSegmentMemberClassName
     */
    public function setStaticSegmentMemberClassName($staticSegmentMemberClassName)
    {
        $this->staticSegmentMemberClassName = $staticSegmentMemberClassName;
    }

    /**
     * @param string $memberClassName
     */
    public function setMemberClassName($memberClassName)
    {
        $this->memberClassName = $memberClassName;
    }

    /**
     * @param StaticSegment[] $items
     *
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->transport->init($items[0]->getChannel()->getTransport());

        foreach ($items as $staticSegment) {
            $this->addStaticListSegment($staticSegment);

            $this->handleMembersUpdate(
                $staticSegment,
                StaticSegmentMember::STATE_ADD,
                'addStaticSegmentMembers',
                StaticSegmentMember::STATE_SYNCED
            );

            $this->handleMembersUpdate(
                $staticSegment,
                StaticSegmentMember::STATE_REMOVE,
                'deleteStaticSegmentMembers',
                StaticSegmentMember::STATE_DROP
            );

            $this->handleMembersUpdate(
                $staticSegment,
                [StaticSegmentMember::STATE_UNSUBSCRIBE, StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE],
                'deleteStaticSegmentMembers'
            );

            // Set unsubscribe to member
            $this->handleMembersUpdate(
                $staticSegment,
                StaticSegmentMember::STATE_UNSUBSCRIBE,
                'batchUnsubscribe',
                StaticSegmentMember::STATE_DROP,
                false,
                Member::STATUS_UNSUBSCRIBED
            );

            $this->handleMembersUpdate(
                $staticSegment,
                StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE,
                'batchUnsubscribe',
                null,
                true
            );
        }
    }

    /**
     * @param StaticSegment $staticSegment
     */
    protected function addStaticListSegment(StaticSegment $staticSegment)
    {
        if (!$staticSegment->getOriginId()) {
            $response = $this->transport->addStaticListSegment(
                [
                    'id' => $staticSegment->getSubscribersList()->getOriginId(),
                    'name' => $staticSegment->getName(),
                ]
            );

            if (!empty($response['id'])) {
                $staticSegment->setOriginId($response['id']);

                $this->logger->debug(sprintf('StaticSegment with id "%s" added', $staticSegment->getOriginId()));

                parent::write([$staticSegment]);
            }
        }
    }

    /**
     * @param StaticSegment $staticSegment
     * @param string|array $segmentStateFilter
     * @param string $method
     * @param string|null $itemState
     * @param bool $deleteMember
     * @param string|null $memberStatus
     */
    public function handleMembersUpdate(
        StaticSegment $staticSegment,
        $segmentStateFilter,
        $method,
        $itemState = null,
        $deleteMember = false,
        $memberStatus = null
    ) {
        $emailsIterator = $this->getSegmentMembersEmailsIterator($staticSegment, $segmentStateFilter);
        if (!$emailsIterator->count()) {
            return;
        }

        $emailsToProcess = [];
        $emailsIterator->next();
        while ($emailsIterator->valid()) {
            $data = $emailsIterator->current();
            $emailsToProcess[$data['staticSegmentMemberId']] = $data['memberEmail'];

            if (count($emailsToProcess) % self::BATCH_SIZE === 0) {
                $this->handleEmailsBatch(
                    $staticSegment,
                    $method,
                    $emailsToProcess,
                    $itemState,
                    $deleteMember,
                    $memberStatus
                );

                $emailsToProcess = [];
            }

            $emailsIterator->next();
        }

        if (count($emailsToProcess)) {
            $this->handleEmailsBatch(
                $staticSegment,
                $method,
                $emailsToProcess,
                $itemState,
                $deleteMember,
                $memberStatus
            );
        }

        if ($deleteMember) {
            $this->deleteListMembers($staticSegment, $segmentStateFilter);
        }
    }

    /**
     * @param StaticSegment $staticSegment
     * @param string $method
     * @param array $emailsToProcess
     * @param string|null $itemState
     * @param bool $deleteMember
     * @param string|null $memberStatus
     */
    protected function handleEmailsBatch(
        StaticSegment $staticSegment,
        $method,
        array $emailsToProcess,
        $itemState = null,
        $deleteMember = false,
        $memberStatus = null
    ) {
        $batchParameters = [
            'id' => $staticSegment->getSubscribersList()->getOriginId(),
            'batch' => array_map(
                function ($email) {
                    return ['email' => $email];
                },
                $emailsToProcess
            ),
        ];

        if ($method === 'addStaticSegmentMembers' || $method === 'deleteStaticSegmentMembers') {
            $batchParameters['seg_id'] = (integer)$staticSegment->getOriginId();
        }
        if ($deleteMember) {
            $batchParameters['delete_member'] = true;
        }

        $response = $this->transport->$method($batchParameters);

        $this->handleResponse(
            $response,
            function ($response, LoggerInterface $logger) use ($staticSegment) {
                $logger->info(
                    sprintf(
                        'Segment #%s [origin_id=%s] Members: [%s] add, [%s] error',
                        $staticSegment->getId(),
                        $staticSegment->getOriginId(),
                        $response['success_count'],
                        $response['error_count']
                    )
                );
            }
        );
        $emailsToUpdate = array_diff($emailsToProcess, $this->getEmailsWithErrors($response));

        if (!$emailsToUpdate) {
            return;
        }

        $this->updateStaticSegmentMembersState($emailsToUpdate, $itemState);

        if ($memberStatus) {
            $this->updateMembersStatus($staticSegment->getSubscribersList(), $emailsToProcess, $memberStatus);
        }
    }

    /**
     * @param StaticSegment $staticSegment
     * @param string|array $state
     *
     * @return BufferedQueryResultIterator
     */
    protected function getSegmentMembersEmailsIterator(StaticSegment $staticSegment, $state)
    {
        $qb = $this->getSegmentMembersQueryBuilder($staticSegment, $state);

        $qb->select('staticSegmentMember.id as staticSegmentMemberId, mmbr.email as memberEmail')
            ->leftJoin('staticSegmentMember.member', 'mmbr');

        $iterator = new BufferedQueryResultIterator($qb);
        $iterator->setBufferSize(self::BATCH_SIZE);
        $iterator->setReverse(true);

        return $iterator;
    }

    /**
     * @param StaticSegment $staticSegment
     * @param string|array $state
     *
     * @return QueryBuilder
     */
    protected function getSegmentMembersQueryBuilder(StaticSegment $staticSegment, $state)
    {
        $qb = $this->getRepository()->createQueryBuilder('staticSegmentMember');

        if (is_array($state)) {
            $stateExpr = $qb->expr()->in('staticSegmentMember.state', ':state');
        } else {
            $stateExpr = $qb->expr()->eq('staticSegmentMember.state', ':state');
        }

        $qb
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('staticSegmentMember.staticSegment', ':staticSegment'),
                    $stateExpr
                )
            )
            ->setParameter('staticSegment', $staticSegment)
            ->setParameter('state', $state);

        return $qb;
    }

    /**
     * @param array $emailsToUpdate
     * @param string|null $itemState
     */
    protected function updateStaticSegmentMembersState($emailsToUpdate, $itemState)
    {
        if ($itemState) {
            $qb = $this->getManager()->createQueryBuilder();
            $qb->update($this->staticSegmentMemberClassName, 'staticSegmentMember')
                ->set('staticSegmentMember.state', ':state')
                ->setParameter('state', $itemState)
                ->where($qb->expr()->in('staticSegmentMember.id', ':ids'))
                ->setParameter('ids', array_keys($emailsToUpdate))
                ->getQuery()
                ->execute();

            foreach ($emailsToUpdate as $id => $email) {
                $this->logger->debug(
                    sprintf(
                        'Member with id "%s" and email "%s" got "%s" state',
                        $id,
                        $email,
                        $itemState
                    )
                );
            }
        }
    }

    /**
     * @param SubscribersList $subscribersList
     * @param array $emailsToUpdate
     * @param string $memberStatus
     */
    protected function updateMembersStatus(SubscribersList $subscribersList, $emailsToUpdate, $memberStatus)
    {
        $qb = $this->getManager()->createQueryBuilder();
        $qb->update($this->memberClassName, 'mmb')
            ->set('mmb.status', ':status')
            ->setParameter('status', $memberStatus)
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('mmb.subscribersList', ':subscribersList'),
                    $qb->expr()->in('mmb.email', ':emails')
                )
            )
            ->setParameter('subscribersList', $subscribersList)
            ->setParameter('emails', array_values($emailsToUpdate))
            ->getQuery()
            ->execute();
    }

    /**
     * @param StaticSegment $staticSegment
     * @param string|array $state
     */
    protected function deleteListMembers(StaticSegment $staticSegment, $state)
    {
        $qb = $this->getSegmentMembersQueryBuilder($staticSegment, $state);
        $qb->select('IDENTITY(staticSegmentMember.member)');

        $deleteQb = $this->getManager()->createQueryBuilder();
        $deleteQb->delete($this->memberClassName, 'listMember')
            ->where(
                $deleteQb->expr()->in('listMember.id', $qb->getDQL())
            )
            ->setParameters($qb->getParameters());

        $deleteQb->getQuery()->execute();
    }

    /**
     * @return EntityRepository
     */
    protected function getRepository()
    {
        if (!$this->staticSegmentMemberClassName) {
            throw new \InvalidArgumentException('Missing StaticSegmentMember class name');
        }

        if (!$this->repository) {
            $this->repository = $this->getManager()->getRepository($this->staticSegmentMemberClassName);
        }

        return $this->repository;
    }

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        if (!$this->manager) {
            $this->manager = $this->registry->getManager();
        }

        return $this->manager;
    }

    /**
     * @param array $response
     * @return array
     */
    protected function getEmailsWithErrors(array $response)
    {
        return array_map(
            function ($item) {
                return $item['email'];
            },
            $this->getArrayData($response, 'errors', 'email')
        );
    }
}
