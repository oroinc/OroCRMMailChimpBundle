<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
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
     * @param string $staticSegmentMemberClassName
     */
    public function setStaticSegmentMemberClassName($staticSegmentMemberClassName)
    {
        $this->staticSegmentMemberClassName = $staticSegmentMemberClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        /** @var StaticSegment $staticSegment */
        $staticSegment = reset($items);
        $channel = $staticSegment->getChannel();

        $this->transport->init($channel->getTransport());

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
            StaticSegmentMember::STATE_UNSUBSCRIBE,
            'batchUnsubscribe',
            StaticSegmentMember::STATE_DROP
        );

        $this->handleMembersUpdate(
            $staticSegment,
            StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE,
            'batchUnsubscribe',
            StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE,
            true
        );
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
     * @param string $segmentStateFilter
     * @param string $method
     * @param string $itemState
     * @param bool $deleteMember
     */
    public function handleMembersUpdate(
        StaticSegment $staticSegment,
        $segmentStateFilter,
        $method,
        $itemState,
        $deleteMember = false
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
                $this->handleEmailsBatch($staticSegment, $method, $emailsToProcess, $itemState, $deleteMember);

                $emailsToProcess = [];
            }

            $emailsIterator->next();
        }

        if (count($emailsToProcess)) {
            $this->handleEmailsBatch($staticSegment, $method, $emailsToProcess, $itemState);
        }
    }

    /**
     * @param StaticSegment $staticSegment
     * @param string $method
     * @param array $emailsToProcess
     * @param string $itemState
     * @param bool $deleteMember
     */
    protected function handleEmailsBatch(
        StaticSegment $staticSegment,
        $method,
        array $emailsToProcess,
        $itemState,
        $deleteMember = false
    ) {
        $response = $this->transport->$method(
            [
                'id' => $staticSegment->getSubscribersList()->getOriginId(),
                'seg_id' => (integer)$staticSegment->getOriginId(),
                'batch' => array_map(
                    function ($email) {
                        return ['email' => $email];
                    },
                    $emailsToProcess
                ),
                'delete_member' => $deleteMember
            ]
        );

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

        $qb = $this->getRepository()->createQueryBuilder('staticSegmentMember');
        $qb
            ->update()
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

    /**
     * @param StaticSegment $staticSegment
     * @param string $state
     *
     * @return BufferedQueryResultIterator
     */
    protected function getSegmentMembersEmailsIterator(StaticSegment $staticSegment, $state)
    {
        $qb = $this->getRepository()->createQueryBuilder('staticSegmentMember');

        $qb
            ->select('staticSegmentMember.id as staticSegmentMemberId, mmbr.email as memberEmail')
            ->leftJoin('staticSegmentMember.member', 'mmbr')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('staticSegmentMember.staticSegment', ':staticSegment'),
                    $qb->expr()->eq('staticSegmentMember.state', ':state')
                )
            )
            ->setParameter('staticSegment', $staticSegment)
            ->setParameter('state', $state);

        $iterator = new BufferedQueryResultIterator($qb);
        $iterator->setBufferSize(self::BATCH_SIZE);
        $iterator->setReverse(true);

        return $iterator;
    }

    /**
     * @return EntityRepository
     */
    protected function getRepository()
    {
        if (!$this->staticSegmentMemberClassName) {
            throw new \InvalidArgumentException('Missing StaticSegmentMember class name');
        }

        return $this->registry->getRepository($this->staticSegmentMemberClassName);
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
