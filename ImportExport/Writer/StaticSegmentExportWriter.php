<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Collections\ArrayCollection;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;

class StaticSegmentExportWriter extends AbstractExportWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        /** @var StaticSegmentMember $item */
        $item = reset($items);

        $staticSegment = $item->getStaticSegment();
        $channel = $staticSegment->getChannel();

        $this->transport->init($channel->getTransport());

        $this->addStaticListSegment($staticSegment);

        $itemsToWrite = [$staticSegment];

        $addedItems = $this->handleMembersUpdate(
            $staticSegment,
            StaticSegmentMember::STATE_ADD,
            'addStaticSegmentMembers',
            StaticSegmentMember::STATE_SYNCED
        );

        $removedItems = $this->handleMembersUpdate(
            $staticSegment,
            StaticSegmentMember::STATE_REMOVE,
            'deleteStaticSegmentMembers',
            StaticSegmentMember::STATE_DROP
        );

        $unsubscribedItems = $this->handleMembersUpdate(
            $staticSegment,
            StaticSegmentMember::STATE_UNSUBSCRIBE,
            'batchUnsubscribe',
            StaticSegmentMember::STATE_DROP
        );

        $unsubscribedDeletedItems = $this->handleMembersUpdate(
            $staticSegment,
            StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE,
            'batchUnsubscribe',
            StaticSegmentMember::STATE_UNSUBSCRIBE_DELETE,
            true
        );

        $itemsToWrite = array_merge(
            $itemsToWrite,
            $addedItems,
            $removedItems,
            $unsubscribedItems,
            $unsubscribedDeletedItems
        );

        parent::write($itemsToWrite);
    }

    /**
     * @param StaticSegment $staticSegment
     * @return null|StaticSegment
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

                return $staticSegment;
            }
        }

        return null;
    }

    /**
     * @param StaticSegment $staticSegment
     * @param $segmentStateFilter
     * @param $method
     * @param $itemState
     * @param bool $deleteMember
     * @return array
     */
    public function handleMembersUpdate(
        StaticSegment $staticSegment,
        $segmentStateFilter,
        $method,
        $itemState,
        $deleteMember = false
    )
    {
        $itemsToWrite = [];

        $items = $staticSegment->getSegmentMembers()
            ->filter(
                function (StaticSegmentMember $segmentMember) use ($segmentStateFilter) {
                    return $segmentMember->getState() === $segmentStateFilter;
                }
            )
            ->toArray();

        if (empty($items)) {
            return [];
        }

        $emails = array_map(
            function (StaticSegmentMember $segmentMember) {
                return $segmentMember->getMember()->getEmail();
            },
            $items
        );

        $response = $this->transport->$method(
            [
                'id' => $staticSegment->getSubscribersList()->getOriginId(),
                'seg_id' => (integer)$staticSegment->getOriginId(),
                'batch' => array_map(
                    function ($email) {
                        return ['email' => $email];
                    },
                    $emails
                ),
                'delete_member' => $deleteMember
            ]
        );

        $this->handleResponse($staticSegment, $response);

        $emailsWithErrors = $this->getArrayData($response, 'errors');

        $items = new ArrayCollection($items);

        $items->filter(
            function (StaticSegmentMember $segmentMember) use ($emailsWithErrors) {
                return !in_array($segmentMember->getMember()->getEmail(), $emailsWithErrors);
            }
        );

        foreach ($items as $item) {
            $item->setState($itemState);

            $itemsToWrite[] = $item;
        }

        return $itemsToWrite;
    }

    /**
     * @param StaticSegment $staticSegment
     * @param mixed $response
     */
    protected function handleResponse(StaticSegment $staticSegment, $response)
    {
        if (!is_array($response)) {
            return;
        }

        if (!$this->logger) {
            return;
        }

        $this->logger->info(
            sprintf(
                'Segment #%s [origin_id=%s] Members: [%s] add, [%s] error',
                $staticSegment->getId(),
                $staticSegment->getOriginId(),
                $response['success_count'],
                $response['error_count']
            )
        );

        if ($response['errors']) {
            foreach ($response['errors'] as $error) {
                $this->logger->warning(
                    sprintf('[Error #%s] %s', $error['code'], $error['error'])
                );
            }
        }
    }
}
