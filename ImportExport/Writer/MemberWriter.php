<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

use Exception;
use Oro\Bundle\MailChimpBundle\Entity\Member;
use Psr\Log\LoggerInterface;

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

        $remoteMergeVars = $this->getSubscribersListMergeVars($item->getSubscribersList());
        $item->getSubscribersList()->setMergeVarConfig($remoteMergeVars);
        $remoteMergeVarsTags = array_map(function (array $var) {
            return $var['tag'];
        }, $remoteMergeVars);

        $membersBySubscriberList = [];
        foreach ($items as $member) {
            $member = $this->filterMergeVars($member, $remoteMergeVarsTags);
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
        $this->stepExecution->setWriteCount($this->stepExecution->getWriteCount() + count($items));
    }

    /**
     * @param string $subscribersListOriginId
     * @param Member[] $items
     * @throws Exception
     */
    protected function batchSubscribe($subscribersListOriginId, array $items)
    {
        $emails = [];
        $members = array_map(
            function (Member $member) use (&$emails) {
                $email = $member->getEmail();
                $emails[] = $email;

                $return = [
                    'email_address' => $email,
                    'status' => 'subscribed',
                ];

                $mergeFields = $member->getMergeVarValues();
                if (is_array($mergeFields) && count($mergeFields) > 0) {
                    $return['merge_fields'] = $mergeFields;
                }

                return $return;
            },
            $items
        );

        $items = array_combine($emails, $items);
        $requestParams = [
            'list_id' => $subscribersListOriginId,
            'members' => $members,
            'double_optin' => false,
            'update_existing' => true,
        ];

        $response = $this->transport->batchSubscribe($requestParams);
        $this
            ->handleResponse(
                $response,
                function ($response, LoggerInterface $logger) use ($subscribersListOriginId, $requestParams) {
                    $logger->info(
                        sprintf(
                            'List [origin_id=%s]: [%s] add, [%s] update, [%s] error',
                            $subscribersListOriginId,
                            $response['total_created'],
                            $response['total_updated'],
                            $response['error_count']
                        )
                    );

                    if (!empty($response['errors']) && is_array($response['errors'])) {
                        $notFakeErrormessages = array_filter($response['errors'], function ($err) {
                            if (false === array_key_exists('error', $err)) {
                                return true;
                            }
                            if (strpos($err['error'], 'fake')) {
                                return false;
                            }
                            if (strpos($err['error'], 'valid')) {
                                return false;
                            }
                            return true;
                        });

                        if (empty($notFakeErrormessages)) {
                            $logger->warning('Mailchimp warning occurs during execution "batchSubscribe" method');
                        } else {
                            $logger->error('Mailchimp error occurs during execution "batchSubscribe" method');
                        }

                        $logger->debug('Mailchimp error occurs during execution "batchSubscribe" method', [
                            'requestParams' => $requestParams,
                        ]);
                    }
                }
            );

        $emailsAdded = $this->getArrayData($response, 'new_members');
        $emailsUpdated = $this->getArrayData($response, 'updated_members');

        foreach (array_merge($emailsAdded, $emailsUpdated) as $emailData) {
            if (!array_key_exists($emailData['email_address'], $items)) {
                $this->logger->alert(
                    sprintf('A member with "%s" email was not found', $emailData['email_address'])
                );

                continue;
            }

            /** @var Member $member */
            $member = $items[$emailData['email_address']];

            $member
                ->setEuid($emailData['unique_email_id'])
                ->setOriginId($emailData['id'])
                ->setStatus(Member::STATUS_SUBSCRIBED);

            $this->logger->debug(
                sprintf('Member with data "%s" successfully processed', json_encode($emailData))
            );
        }
    }

    /**
     * @param Member $member
     * @param array $remoteMergeVarTags
     *
     * @return Member
     */
    protected function filterMergeVars(Member $member, array $remoteMergeVarTags)
    {
        return $member->setMergeVarValues(array_filter(
            $member->getMergeVarValues(),
            function ($key) use ($remoteMergeVarTags) {
                return in_array($key, $remoteMergeVarTags, true);
            },
            ARRAY_FILTER_USE_KEY
        ));
    }
}
