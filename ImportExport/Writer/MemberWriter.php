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

        $itemsToSave = $this->batchSubscribe($subscribersList, $items);
        if ($itemsToSave) {
            $itemsToWrite = array_merge($itemsToWrite, $itemsToSave);
        }

        parent::write($itemsToWrite);
    }

    /**
     * @param SubscribersList $subscribersList
     * @param array|ArrayCollection $items
     * @return array
     */
    protected function batchSubscribe(SubscribersList $subscribersList, array $items)
    {
        $itemsToWrite = [];

        $emails = array_map(
            function (Member $member) {
                return [
                    'email' => ['email' => $member->getEmail()],
                    'merge_vars' => $member->getMergeVarValues()
                ];
            },
            $items
        );

        $response = $this->transport->batchSubscribe(
            [
                'id' => $subscribersList->getOriginId(),
                'batch' => $emails,
                'double_optin' => false,
                'update_existing' => true,
            ]
        );

        $this->handleResponse($response);

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

            if ($member) {
                $member
                    ->setEuid($emailData['euid'])
                    ->setLeid($emailData['leid'])
                    ->setStatus(Member::STATUS_SUBSCRIBED);

                $itemsToWrite[] = $member;

                $this->logger->debug(sprintf('Member with data "%s" successfully processed', json_encode($emailData)));
            } else {
                $this->logger->warning(sprintf('A member with "%s" email was not found', $emailData['email']));
            }
        }

        return $itemsToWrite;
    }

    /**
     * @param array $response
     */
    protected function handleResponse(array $response)
    {
        if (!empty($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $this->logger->warning(
                    sprintf('[Error #%s] %s', $error['code'], $error['error'])
                );
            }
        }
    }
}
