<?php

namespace Oro\Bundle\MailChimpBundle\Client;

use DrewM\MailChimp\MailChimp;
use Exception;
use Oro\Bundle\MailChimpBundle\Exception\MailChimpClientException;

class MailChimpClient
{
    /**
     * Max timeout in mailchimp.
     * Used in curl, integer in seconds.
     */
    const MAILCHIMP_MAX_TIMEOUT = 300;

    /**
     * The Mailchimp integration API key
     * @var string
     */
    private $apiKey;

    /**
     * MailChimpClient constructor.
     *
     * @param string $apiKey
     * @throws Exception
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new MailChimp($apiKey);
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/ping/
     * @return array|false
     * @throws MailChimpClientException
     */
    public function ping()
    {
        $result = $this->client->get('ping');
        if (false === $result) {
            throw new Exception('Api key invalid');
        }
        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     *
     * @param array $result
     * @throws MailChimpClientException
     */
    public function guardAgainstStatus($result)
    {
        if (false === is_array($result)) {
            throw MailChimpClientException::becauseResultIsNotAnArray();
        }

        if (array_key_exists('status', $result) && (int)$result['status'] > 0) {
            $code = $result['status'];
            $detail = isset($result['detail']) ? $result['detail'] : '';

            if (array_key_exists('errors', $result) && is_array($result['errors'])) {
                $errors = [];
                foreach ($result['errors'] as $error) {
                    if (false === is_array($error)) {
                        continue;
                    }

                    if (array_key_exists('message', $error)) {
                        $errors[] = $error['message'];
                    }

                    if (array_key_exists('error', $error)) {
                        if (array_key_exists('email_address', $error)) {
                            $errors[] = sprintf(
                                'Error: %s, Email: %s',
                                $error['error'],
                                $error['email_address']
                            );
                        } else {
                            $errors[] = sprintf(
                                'Error: %s',
                                $error['error']
                            );
                        }
                    }
                }

                if (count($errors) > 0) {
                    $detail .= ' | ' . implode(';', $errors);
                }
            }

            throw MailChimpClientException::becauseStatusIsIncorect($code, $detail);
        }
    }

    /**
     * @param array $options
     * @return string
     * @throws MailChimpClientException
     */
    public function getListId(array $options)
    {
        if (array_key_exists('list_id', $options)) {
            return $options['list_id'];
        }

        if (array_key_exists('id', $options)) {
            return $options['id'];
        }

        throw MailChimpClientException::becauseListIdWasNotFound();
    }

    /**
     * @param array $options
     * @return string
     * @throws MailChimpClientException
     */
    public function getCampaignId(array $options)
    {
        if (array_key_exists('campaign_id', $options)) {
            return $options['campaign_id'];
        }

        throw MailChimpClientException::becauseCampaignIdWasNotFound();
    }

    /**
     * @param array $options
     * @return string
     * @throws MailChimpClientException
     */
    public function getStaticSegmentId(array $options)
    {
        if (array_key_exists('static_segment_id', $options) && $options['static_segment_id']) {
            return $options['static_segment_id'];
        }

        throw MailChimpClientException::becauseStaticSegmentIdWasNotFound();
    }

    /**
     * @param array $members
     * @return array
     */
    private function getUniqueMembers(array $members)
    {
        return array_reduce($members, function ($result, $member) {
            foreach ($result as $item) {
                if (empty($item['email_address'])) {
                    return $result;
                }
                if (strtolower($item['email_address']) === strtolower($member['email_address'])) {
                    return $result;
                }
            }

            $result[] = $member;
            return $result;
        }, []);
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/#%20
     * @param array $options
     * @return array
     * @throws MailChimpClientException
     */
    public function getLists(array $options)
    {
        $result = $this->client->get('lists', $options);

        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#%20
     * @param string $listId
     * @return array
     * @throws MailChimpClientException
     */
    public function getListMergeVars(string $listId)
    {
        $result = $this->client->get("lists/$listId/merge-fields");
        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#%20
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function addListMergeVar(array $options)
    {
        $listId = $this->getListId($options);

        $result = $this->client->post("lists/$listId/merge-fields", $options);
        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#%20
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function getListStaticSegments(array $options)
    {
        $listId = $this->getListId($options);

        $result = $this->client->get("lists/$listId/segments", $options);
        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function addStaticListSegment(array $options)
    {
        $listId = $this->getListId($options);

        $result = $this->client->post("lists/$listId/segments", [
            'name' => $options['name'],
            'static_segment' => [],
        ]);

        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function batchSubscribe(array $options)
    {
        $listId = $this->getListId($options);
        $options['members'] = $this->getUniqueMembers($options['members']);

        $result = $this->client->post("lists/$listId", $options, self::MAILCHIMP_MAX_TIMEOUT);

        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/#%20
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function getCampaigns(array $options)
    {
        $result = $this->client->get('campaigns', $options);

        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/#%20
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function getCampaignReport(string $campaignId)
    {
        $result = $this->client->get("reports/$campaignId");

        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function getCampaignUnsubscribesReport(array $options)
    {
        $campaignId = $this->getCampaignId($options);

        $result = $this->client->get("reports/$campaignId/unsubscribed", $options);
        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function getCampaignSentToReport(array $options)
    {
        $campaignId = $this->getCampaignId($options);

        $result = $this->client->get("reports/$campaignId/sent-to", $options);
        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function getCampaignAbuseReport(array $options)
    {
        $campaignId = $this->getCampaignId($options);

        $result = $this->client->get("reports/$campaignId/abuse-reports", $options);
        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/
     *
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function batchUnsubscribe(array $options)
    {
        $listId = $this->getListId($options);
        $options['members'] = $this->getUniqueMembers($options['members']);

        $result = $this->client->post("lists/$listId", $options, self::MAILCHIMP_MAX_TIMEOUT);
        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#%20
     * @param array $options
     * @return array|false
     * @throws MailChimpClientException
     */
    public function deleteListMergeVar(array $options)
    {
        $listId = $this->getListId($options);

        $result = $this->client->delete("lists/$listId/merge-fields", $options);
        $this->guardAgainstStatus($result);

        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#%20
     *
     * @param array $options
     * @return array
     * @throws MailChimpClientException
     */
    public function updateListMember(array $options)
    {
        $listId = $this->getListId($options);
        $subriberHash = md5(strtolower($options['email']));

        $result = $this->client->patch("lists/$listId/members/$subriberHash", $options);
        $this->guardAgainstStatus($result);
        return $result;
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/members/#%20
     *
     * @param array $options
     * @return array
     * @throws MailChimpClientException
     */
    public function addStaticSegmentMembers(array $options)
    {
        $listId = $this->getListId($options);
        $segmentId = $this->getStaticSegmentId($options);

        return $this->client->post("lists/$listId/segments/$segmentId", $options);
    }

    /**
     * @link https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/members/#%20
     *
     * @param array $options
     * @return array
     * @throws MailChimpClientException
     */
    public function deleteStaticSegmentMembers(array $options)
    {
        $listId = $this->getListId($options);
        $segmentId = $this->getStaticSegmentId($options);

        return $this->client->post("lists/$listId/segments/$segmentId", $options);
    }
}
