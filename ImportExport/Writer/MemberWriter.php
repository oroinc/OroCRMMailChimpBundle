<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MailChimpBundle\Entity\Member;

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

        $membersBySubscriberList = [];
        foreach ($items as $member) {
            $membersBySubscriberList[$member->getSubscribersList()->getOriginId()][] = $member;
        }

        foreach ($membersBySubscriberList as $subscribersListOriginId => $members) {
            $this->batchSubscribe($subscribersListOriginId, $members);
        }

        array_walk(
            $items,
            function (Member $member) {
                if ($member->getStatus() === Member::STATUS_EXPORT) {
                    $member->setStatus(Member::STATUS_EXPORT_FAILED);
                }
            }
        );

        parent::write($items);

        $this->logger->info(sprintf('%d members processed', count($items)));
    }

    /**
     * @param string $subscribersListOriginId
     * @param Member[] $items
     */
    protected function batchSubscribe($subscribersListOriginId, array $items)
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
                'id' => $subscribersListOriginId,
                'batch' => $batch,
                'double_optin' => false,
                'update_existing' => true,
            ]
        );

        $this
            ->handleResponse(
                $response,
                function ($response, LoggerInterface $logger) use ($subscribersListOriginId) {
                    $logger->info(
                        sprintf(
                            'List [origin_id=%s]: [%s] add, [%s] update, [%s] error',
                            $subscribersListOriginId,
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
