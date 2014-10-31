<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Oro\Bundle\UIBundle\Tools\ArrayUtils;
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

        $itemsToWrite = array_merge($itemsToWrite, $addedItems, $removedItems);

        $staticSegment->setSyncStatus(StaticSegment::STATUS_SYNCED);

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
     * @param string $segmentStateFilter
     * @param string $method
     * @param string $itemState
     * @return StaticSegmentMember[]
     */
    public function handleMembersUpdate(StaticSegment $staticSegment, $segmentStateFilter, $method, $itemState)
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
                )
            ]
        );

        $this->handleResponse($staticSegment, $response);

        $emailsWithErrors = [];
        if (!empty($response['errors'])) {
            $emailsWithErrors = ArrayUtils::arrayColumn($response['errors'], 'email');
        }

        /** @var StaticSegmentMember $item */
        foreach ($items as $item) {
            if (!in_array($item->getMember()->getEmail(), $emailsWithErrors)) {
                $item->setState($itemState);

                $itemsToWrite[] = $item;
            }
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
