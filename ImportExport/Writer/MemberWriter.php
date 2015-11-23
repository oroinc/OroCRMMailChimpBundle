<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Psr\Log\LoggerInterface;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

class MemberWriter extends AbstractExportWriter
{
    /**
     * @param Member[] $items
     *
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        /** @var Member $item */
        $item = $items[0];
        $this->transport->init($item->getChannel()->getTransport());

        $subscribersList = $item->getSubscribersList();

        $this->batchSubscribe($subscribersList, $items);

        array_walk(
            $items,
            function (Member $member) {
                if ($member->getStatus() === Member::STATUS_EXPORT) {
                    $member->setStatus(Member::STATUS_EXPORT_FAILED);
                }
            }
        );

        parent::write($items);

        $this->logger->info(sprintf('%d items written', count($items)));
    }

    /**
     * @param SubscribersList $subscribersList
     * @param Member[] $items
     */
    protected function batchSubscribe(SubscribersList $subscribersList, array $items)
    {
        $emails = [];

        $batch = array_map(
            function (Member $member) use (&$emails) {
                $email = $member->getEmail();
                $emails[] = $email;

                return [
                    'email' => ['email' => $email],
                    'merge_vars' => $member->getMergeVarValues(),
                ];
            },
            $items
        );

        $items = array_combine($emails, $items);

        $response = $this->transport->batchSubscribe(
            [
                'id' => $subscribersList->getOriginId(),
                'batch' => $batch,
                'double_optin' => false,
                'update_existing' => true,
            ]
        );

        $this
            ->handleResponse(
                $response,
                function ($response, LoggerInterface $logger) use ($subscribersList) {
                    $logger->info(
                        sprintf(
                            'List #%s [origin_id=%s]: [%s] add, [%s] update, [%s] error',
                            $subscribersList->getId(),
                            $subscribersList->getOriginId(),
                            $response['add_count'],
                            $response['update_count'],
                            $response['error_count']
                        )
                    );
                }
            );

        $emailsAdded = $this->getArrayData($response, 'adds');
        $emailsUpdated = $this->getArrayData($response, 'updates');

        foreach (array_merge($emailsAdded, $emailsUpdated) as $emailData) {
            if (!array_key_exists($emailData['email'], $items)) {
                $this->logger->alert(sprintf('A member with "%s" email was not found', $emailData['email']));

                continue;
            }

            /** @var Member $member */
            $member = $items[$emailData['email']];

            $member
                ->setEuid($emailData['euid'])
                ->setLeid($emailData['leid'])
                ->setStatus(Member::STATUS_SUBSCRIBED);

            $this->logger->debug(sprintf('Member with data "%s" successfully processed', json_encode($emailData)));
        }
    }
}
