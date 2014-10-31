<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Collections\ArrayCollection;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

class MemberWriter extends AbstractExportWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $itemsToWrite = [];

        /** @var Member $item */
        $item = reset($items);
        $this->transport->init($item->getChannel()->getTransport());

        $subscribersList = $item->getSubscribersList();

        if ($itemsToSave = $this->batchSubscribe($subscribersList, $items)) {

        }

        parent::write($itemsToWrite);
    }

    /**
     * @param SubscribersList $subscribersList
     * @param array $items
     * @return array
     */
    protected function batchSubscribe(SubscribersList $subscribersList, array $items)
    {
        $itemsToWrite = [];

        $emails = array_map(
            function (Member $member) {
                return ['email' => ['email' => $member->getEmail()]];
            },
            $items
        );

        $response = $this->transport->batchSubscribe(
            [
                'id' => $subscribersList->getOriginId(),
                'batch' => $emails,
                'double_optin' => false
            ]
        );

        $this->handleResponse($subscribersList, $response);

        $emailsAdded = $this->getArrayData($response, 'adds');
        $emailsUpdated = $this->getArrayData($response, 'updates');

        $items = new ArrayCollection($items);
        foreach (array_merge($emailsAdded, $emailsUpdated) as $emailData) {
            /** @var Member $member */
            $member = $items
                ->filter(
                    function (Member $member) use ($emailData) {
                        return $member->getEmail() === $emailData['email'];
                    }
                )
            ->first();

            $member
                ->setEuid($emailData['euid'])
                ->setLeid($emailData['leid'])
                ->setStatus(Member::STATUS_SUBSCRIBED);

            $itemsToWrite[] = $member;
        }

        return $itemsToWrite;
    }

    /**
     * @param SubscribersList $subscribersList
     * @param $response
     */
    protected function handleResponse(SubscribersList $subscribersList, $response)
    {
        if (!is_array($response)) {
            return;
        }
        if (!$this->logger) {
            return;
        }

        $this->logger->info(
            sprintf(
                'List #%s [origin_id=%s]: [%s] add, [%s] update, [%s] error',
                $subscribersList->getId(),
                $subscribersList->getOriginId(),
                $response['add_count'],
                $response['update_count'],
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
