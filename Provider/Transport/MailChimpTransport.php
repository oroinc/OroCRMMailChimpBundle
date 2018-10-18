<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport;

use DateTime;
use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;
use Iterator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\MailChimpBundle\Entity\Campaign;
use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MailChimpBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository;
use Oro\Bundle\MailChimpBundle\Entity\Repository\SubscribersListRepository;
use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;
use Oro\Bundle\MailChimpBundle\Entity\Template;
use Oro\Bundle\MailChimpBundle\Exception\MailChimpClientException;
use Oro\Bundle\MailChimpBundle\Exception\RequiredOptionException;
use Oro\Bundle\MailChimpBundle\Form\Type\IntegrationSettingsType;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\CampaignIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\ListIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberAbuseIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberActivityIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberSentToIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberUnsubscribesIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\StaticSegmentIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\StaticSegmentListIterator;
use Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\TemplateIterator;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @link http://apidocs.mailchimp.com/api/2.0/
 * @link https://bitbucket.org/mailchimp/mailchimp-api-php/
 */
class MailChimpTransport implements TransportInterface
{
    /**#@+
     * @const string Constants related to datetime representation in MailChimp
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';
    const TIMEZONE = 'UTC';
    /**#@-*/

    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @var MailChimpClient[]
     */
    protected $clients = [];

    /**
     * @var MailChimpClientFactory
     */
    protected $mailChimpClientFactory;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param MailChimpClientFactory $mailChimpClientFactory
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(MailChimpClientFactory $mailChimpClientFactory, ManagerRegistry $managerRegistry)
    {
        $this->mailChimpClientFactory = $mailChimpClientFactory;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function init(Transport $transportEntity)
    {
        $apiKey = $transportEntity->getSettingsBag()->get('apiKey');
        if (!$apiKey) {
            throw new RequiredOptionException('apiKey');
        }

        if (array_key_exists($apiKey, $this->clients)) {
            $this->client = $this->clients[$apiKey];

            return;
        }

        $this->clients[$apiKey] = $this->mailChimpClientFactory->create($apiKey);
        $this->client = $this->clients[$apiKey];
    }

    /**
     * @link http://apidocs.mailchimp.com/api/2.0/campaigns/list.php
     * @param Channel $channel
     * @param string|null $status Constant of \Oro\Bundle\MailChimpBundle\Entity\Campaign::STATUS_XXX
     * @param bool|null $usesSegment
     * @return Iterator
     */
    public function getCampaigns(Channel $channel, $status = null, $usesSegment = null)
    {
        $filters = [];
        if (null !== $status) {
            $filters['status'] = $status;
        }
        if (null !== $usesSegment) {
            $filters['uses_segment'] = (bool)$usesSegment;
        }

        // Synchronize only campaigns that are connected to subscriber lists that are used within Oro.
        /** @var StaticSegmentRepository $repository */
        $repository = $this->managerRegistry->getRepository('OroMailChimpBundle:StaticSegment');
        $staticSegments = $repository->getStaticSegments($channel);

        $listsToSynchronize = [];
        foreach ($staticSegments as $staticSegment) {
            $listsToSynchronize[] = $staticSegment->getSubscribersList()->getOriginId();
        }
        $listsToSynchronize = array_unique($listsToSynchronize);

        if (!$listsToSynchronize) {
            return new \ArrayIterator();
        }

        $filters['list_ids'] = $listsToSynchronize;
        $filters['exact'] = false;

        return new CampaignIterator($this->client, $filters);
    }

    /**
     * @link http://apidocs.mailchimp.com/api/2.0/lists/list.php
     * @return Iterator
     */
    public function getLists()
    {
        return new ListIterator($this->client);
    }

    /**
     * Get list of MailChimp Templates.
     *
     * @link http://apidocs.mailchimp.com/api/2.0/templates/list.php
     * @return Iterator
     */
    public function getTemplates()
    {
        $parameters = [
            'types' => [
                Template::TYPE_USER => true,
                Template::TYPE_GALLERY => true,
                Template::TYPE_BASE => true,
            ],
            'filters' => [
                'include_drag_and_drop' => true,
            ],
        ];

        return new TemplateIterator($this->client, $parameters);
    }

    /**
     * @link http://apidocs.mailchimp.com/api/2.0/lists/static-segments.php
     * @param SubscribersList $list
     * @return StaticSegmentIterator
     */
    public function getListStaticSegments(SubscribersList $list)
    {
        $iterator = new StaticSegmentIterator($this->client);
        $iterator->setSubscriberListId($list->getOriginId());

        return $iterator;
    }

    /**
     * @link https://apidocs.mailchimp.com/api/2.0/lists/merge-vars.php
     *
     * @param string $listId
     * @return array
     * @throws MailChimpClientException
     */
    public function getListMergeVars(string $listId)
    {
        return $this->client->getListMergeVars($listId);
    }

    /**
     * @link https://apidocs.mailchimp.com/api/2.0/lists/merge-var-add.php
     *
     * @param array $args
     * @return array
     * @throws MailChimpClientException
     */
    public function addListMergeVar(array $args)
    {
        return $this->client->addListMergeVar($args);
    }

    /**
     * @link https://apidocs.mailchimp.com/api/2.0/lists/merge-var-del.php
     *
     * @param array $args
     * @return array
     * @throws MailChimpClientException
     */
    public function deleteListMergeVar(array $args)
    {
        return $this->client->deleteListMergeVar($args);
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#create-post_lists_list_id
     *
     * @param array $args
     *
     * @return array
     * @throws MailChimpClientException
     */
    public function batchSubscribe(array $args)
    {
        return $this->client->batchSubscribe($args);
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#create-post_lists_list_id
     * @param array $args
     *
     * @return array
     * @throws MailChimpClientException
     */
    public function batchUnsubscribe(array $args)
    {
        return $this->client->batchUnsubscribe($args);
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#create-post_lists_list_id_segments
     *
     * @param array $args
     *
     * @return array
     * @throws MailChimpClientException
     */
    public function addStaticListSegment(array $args)
    {
        return $this->client->addStaticListSegment($args);
    }

    /**
     * @link https://apidocs.mailchimp.com/api/2.0/lists/update-member.php
     *
     * @param array $args
     * @return array
     * @throws MailChimpClientException
     */
    public function updateListMember(array $args)
    {
        return $this->client->updateListMember($args);
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param DateTime $since
     * @param string $interval
     * @return string
     * @throws Exception
     */
    protected function getSinceForApi(DateTime $since, $interval = 'PT1S')
    {
        if ($interval) {
            $since = clone $since;
            $since->setTimezone(new \DateTimeZone('UTC'));
            $since->sub(new \DateInterval($interval));
        }

        return $since->format(self::DATETIME_FORMAT);
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return IntegrationSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return \Oro\Bundle\MailChimpBundle\Entity\MailChimpTransport::class;
    }

    /**
     * Get all members from MailChimp that requires update.
     *
     * @link http://apidocs.mailchimp.com/export/1.0/list.func.php
     *
     * @param Channel $channel
     * @param DateTime|null $since
     * @return Iterator
     * @throws Exception
     */
    public function getMembersToSync(Channel $channel, DateTime $since = null)
    {
        /** @var SubscribersListRepository $subscribersListRepository */
        $subscribersListRepository = $this->managerRegistry->getRepository('OroMailChimpBundle:SubscribersList');
        $subscribersLists = $subscribersListRepository->getUsedSubscribersListIterator($channel);

        $parameters = ['status' => [Member::STATUS_SUBSCRIBED, Member::STATUS_UNSUBSCRIBED, Member::STATUS_CLEANED]];

        if ($since) {
            $parameters['since'] = $this->getSinceForApi($since);
        }

        $memberIterator = new MemberIterator($subscribersLists, $this->client, $parameters);

        if ($this->logger) {
            $memberIterator->setLogger($this->logger);
        }

        return $memberIterator;
    }

    /**
     * @param Channel $channel
     * @param array[] $sinceMap
     * @return MemberActivityIterator
     * @throws Exception
     */
    public function getMemberActivitiesToSync(Channel $channel, array $sinceMap = null)
    {
        $parameters = ['include_empty' => false];
        if ($sinceMap) {
            foreach ($sinceMap as $campaign => $since) {
                // Seems that MailChimp has delay on activities collecting
                // and activities list may be extended in past
                $sinceDate = min($since);
                if (!$sinceDate) {
                    $sinceDate = max($since);
                }
                if ($sinceDate) {
                    $sinceMap[$campaign]['since'] = $this->getSinceForApi($sinceDate, 'PT10M');
                }
            }
        }

        return new MemberActivityIterator(
            $this->getSentCampaignsIterator($channel),
            $this->client,
            $parameters,
            $sinceMap
        );
    }



    // ** NOT CALLED ** //


    // CAMPAIGNS

    /**
     * @param Channel $channel
     * @return Iterator
     */
    protected function getSentCampaignsIterator(Channel $channel)
    {
        /** @var CampaignRepository $repository */
        $repository = $this->managerRegistry->getRepository('OroMailChimpBundle:Campaign');
        return $repository->getSentCampaigns($channel);
    }

    /**
     * @param Channel $channel
     * @return Iterator
     */
    public function getCampaignUnsubscribesReport(Channel $channel)
    {
        return new MemberUnsubscribesIterator($this->getSentCampaignsIterator($channel), $this->client);
    }

    /**
     * @param Channel $channel
     * @return Iterator
     */
    public function getCampaignSentToReport(Channel $channel)
    {
        return new MemberSentToIterator($this->getSentCampaignsIterator($channel), $this->client);
    }

    /**
     * @param Channel $channel
     * @param DateTime $since
     * @return Iterator
     * @throws Exception
     */
    public function getCampaignAbuseReport(Channel $channel, DateTime $since = null)
    {
        if ($since) {
            $since = $this->getSinceForApi($since);
        }

        return new MemberAbuseIterator($this->getSentCampaignsIterator($channel), $since, $this->client);
    }


    // SEGMENTS

    /**
     * @link http://apidocs.mailchimp.com/api/2.0/lists/static-segments.php
     * @param Channel $channel
     * @return StaticSegmentListIterator
     */
    public function getSegmentsToSync(Channel $channel)
    {
        /** @var SubscribersListRepository $subscribersListRepository */
        $subscribersListRepository = $this->managerRegistry->getRepository('OroMailChimpBundle:SubscribersList');
        $subscribersLists = $subscribersListRepository->getUsedSubscribersListIterator($channel);

        return new StaticSegmentListIterator($subscribersLists, $this->client);
    }

    /**
     * @link http://apidocs.mailchimp.com/api/2.0/lists/static-segment-members-add.php
     *
     * @param array $args
     *
     * @return array
     * @throws MailChimpClientException
     */
    public function addStaticSegmentMembers(array $args)
    {
        return $this->client->addStaticSegmentMembers($args);
    }

    /**
     * @link http://apidocs.mailchimp.com/api/2.0/lists/static-segment-members-del.php
     *
     * @param array $args
     *
     * @return array
     * @throws MailChimpClientException
     */
    public function deleteStaticSegmentMembers(array $args)
    {
        return $this->client->deleteStaticSegmentMembers($args);
    }



    // TEMPLATES

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.mailchimp.integration_transport.label';
    }
}
