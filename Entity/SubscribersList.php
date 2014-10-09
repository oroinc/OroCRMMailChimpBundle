<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *\
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\MailChimpBundle\Entity\Repository\SubscribersListRepository")
 * @ORM\Table(
 *      name="orocrm_mailchimp_list"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-group"
 *      }
 *  }
 * )
 */
class SubscribersList implements OriginAwareInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="origin_id", type="string", length=32, nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $originId;

    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $channel;

    /**
     * @var MarketingList
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MarketingListBundle\Entity\MarketingList", cascade={"persist"})
     * @ORM\JoinColumn(name="marketing_list_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $marketingList;

    /**
     * @var int
     * @ORM\Column(name="web_id", type="bigint", nullable=false)
     */
    protected $webId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var bool
     * @ORM\Column(name="email_type_option", type="boolean")
     */
    protected $emailTypeOption;

    /**
     * @var bool
     * @ORM\Column(name="use_awesomebar", type="boolean")
     */
    protected $useAwesomeBar;

    /**
     * @var string
     *
     * @ORM\Column(name="default_from_name", type="string", length=255, nullable=true)
     */
    protected $defaultFromName;

    /**
     * @var string
     *
     * @ORM\Column(name="default_from_email", type="string", length=255, nullable=true)
     */
    protected $defaultFromEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="default_subject", type="string", length=255, nullable=true)
     */
    protected $defaultSubject;

    /**
     * @var string
     *
     * @ORM\Column(name="default_language", type="string", length=50, nullable=true)
     */
    protected $defaultLanguage;

    /**
     * @var float
     *
     * @ORM\Column(name="list_rating", type="float", nullable=true)
     */
    protected $listRating;

    /**
     * @var string
     *
     * @ORM\Column(name="subscribe_url_short", type="text", nullable=true)
     */
    protected $subscribeUrlShort;

    /**
     * @var string
     *
     * @ORM\Column(name="subscribe_url_long", type="text", nullable=true)
     */
    protected $subscribeUrlLong;

    /**
     * @var string
     *
     * @ORM\Column(name="beamer_address", type="string", length=255, nullable=true)
     */
    protected $beamerAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="visibility", type="text", nullable=true)
     */
    protected $visibility;

    /**
     * @var float
     *
     * @ORM\Column(name="member_count", type="float", nullable=true)
     */
    protected $memberCount;

    /**
     * @var float
     *
     * @ORM\Column(name="unsubscribe_count", type="float", nullable=true)
     */
    protected $unsubscribeCount;

    /**
     * @var float
     *
     * @ORM\Column(name="cleaned_count", type="float", nullable=true)
     */
    protected $cleanedCount;

    /**
     * @var float
     *
     * @ORM\Column(name="member_count_since_send", type="float", nullable=true)
     */
    protected $memberCountSinceSend;

    /**
     * @var float
     *
     * @ORM\Column(name="unsubscribe_count_since_send", type="float", nullable=true)
     */
    protected $unsubscribeCountSinceSend;

    /**
     * @var float
     *
     * @ORM\Column(name="cleaned_count_since_send", type="float", nullable=true)
     */
    protected $cleanedCountSinceSend;

    /**
     * @var float
     *
     * @ORM\Column(name="campaign_count", type="float", nullable=true)
     */
    protected $campaignCount;

    /**
     * @var float
     *
     * @ORM\Column(name="grouping_count", type="float", nullable=true)
     */
    protected $groupingCount;

    /**
     * @var float
     *
     * @ORM\Column(name="group_count", type="float", nullable=true)
     */
    protected $groupCount;

    /**
     * @var float
     *
     * @ORM\Column(name="merge_var_count", type="float", nullable=true)
     */
    protected $mergeVarCount;

    /**
     * @var float
     *
     * @ORM\Column(name="avg_sub_rate", type="float", nullable=true)
     */
    protected $avgSubRate;

    /**
     * @var float
     *
     * @ORM\Column(name="avg_unsub_rate", type="float", nullable=true)
     */
    protected $avgUsubRate;

    /**
     * @var float
     *
     * @ORM\Column(name="target_sub_rate", type="float", nullable=true)
     */
    protected $targetSubRate;

    /**
     * @var float
     *
     * @ORM\Column(name="open_rate", type="float", nullable=true)
     */
    protected $openRate;

    /**
     * @var float
     *
     * @ORM\Column(name="click_rate", type="float", nullable=true)
     */
    protected $clickRate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOriginId()
    {
        return $this->originId;
    }

    /**
     * @param string $originId
     * @return SubscribersList
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;

        return $this;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     * @return SubscribersList
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return MarketingList
     */
    public function getMarketingList()
    {
        return $this->marketingList;
    }

    /**
     * @param MarketingList $marketingList
     * @return SubscribersList
     */
    public function setMarketingList($marketingList)
    {
        $this->marketingList = $marketingList;

        return $this;
    }

    /**
     * @return int
     */
    public function getWebId()
    {
        return $this->webId;
    }

    /**
     * @param int $webId
     * @return SubscribersList
     */
    public function setWebId($webId)
    {
        $this->webId = $webId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return SubscribersList
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isEmailTypeOption()
    {
        return $this->emailTypeOption;
    }

    /**
     * @param boolean $emailTypeOption
     * @return SubscribersList
     */
    public function setEmailTypeOption($emailTypeOption)
    {
        $this->emailTypeOption = $emailTypeOption;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isUseAwesomeBar()
    {
        return $this->useAwesomeBar;
    }

    /**
     * @param boolean $useAwesomeBar
     * @return SubscribersList
     */
    public function setUseAwesomeBar($useAwesomeBar)
    {
        $this->useAwesomeBar = $useAwesomeBar;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultFromName()
    {
        return $this->defaultFromName;
    }

    /**
     * @param string $defaultFromName
     * @return SubscribersList
     */
    public function setDefaultFromName($defaultFromName)
    {
        $this->defaultFromName = $defaultFromName;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultFromEmail()
    {
        return $this->defaultFromEmail;
    }

    /**
     * @param string $defaultFromEmail
     * @return SubscribersList
     */
    public function setDefaultFromEmail($defaultFromEmail)
    {
        $this->defaultFromEmail = $defaultFromEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultSubject()
    {
        return $this->defaultSubject;
    }

    /**
     * @param string $defaultSubject
     * @return SubscribersList
     */
    public function setDefaultSubject($defaultSubject)
    {
        $this->defaultSubject = $defaultSubject;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * @param string $defaultLanguage
     * @return SubscribersList
     */
    public function setDefaultLanguage($defaultLanguage)
    {
        $this->defaultLanguage = $defaultLanguage;
        return $this;
    }

    /**
     * @return float
     */
    public function getListRating()
    {
        return $this->listRating;
    }

    /**
     * @param float $listRating
     * @return SubscribersList
     */
    public function setListRating($listRating)
    {
        $this->listRating = $listRating;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubscribeUrlShort()
    {
        return $this->subscribeUrlShort;
    }

    /**
     * @param string $subscribeUrlShort
     * @return SubscribersList
     */
    public function setSubscribeUrlShort($subscribeUrlShort)
    {
        $this->subscribeUrlShort = $subscribeUrlShort;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubscribeUrlLong()
    {
        return $this->subscribeUrlLong;
    }

    /**
     * @param string $subscribeUrlLong
     * @return SubscribersList
     */
    public function setSubscribeUrlLong($subscribeUrlLong)
    {
        $this->subscribeUrlLong = $subscribeUrlLong;
        return $this;
    }

    /**
     * @return string
     */
    public function getBeamerAddress()
    {
        return $this->beamerAddress;
    }

    /**
     * @param string $beamerAddress
     * @return SubscribersList
     */
    public function setBeamerAddress($beamerAddress)
    {
        $this->beamerAddress = $beamerAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     * @return SubscribersList
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * @return float
     */
    public function getMemberCount()
    {
        return $this->memberCount;
    }

    /**
     * @param float $memberCount
     * @return SubscribersList
     */
    public function setMemberCount($memberCount)
    {
        $this->memberCount = $memberCount;
        return $this;
    }

    /**
     * @return float
     */
    public function getUnsubscribeCount()
    {
        return $this->unsubscribeCount;
    }

    /**
     * @param float $unsubscribeCount
     * @return SubscribersList
     */
    public function setUnsubscribeCount($unsubscribeCount)
    {
        $this->unsubscribeCount = $unsubscribeCount;
        return $this;
    }

    /**
     * @return float
     */
    public function getCleanedCount()
    {
        return $this->cleanedCount;
    }

    /**
     * @param float $cleanedCount
     * @return SubscribersList
     */
    public function setCleanedCount($cleanedCount)
    {
        $this->cleanedCount = $cleanedCount;
        return $this;
    }

    /**
     * @return float
     */
    public function getMemberCountSinceSend()
    {
        return $this->memberCountSinceSend;
    }

    /**
     * @param float $memberCountSinceSend
     * @return SubscribersList
     */
    public function setMemberCountSinceSend($memberCountSinceSend)
    {
        $this->memberCountSinceSend = $memberCountSinceSend;
        return $this;
    }

    /**
     * @return float
     */
    public function getUnsubscribeCountSinceSend()
    {
        return $this->unsubscribeCountSinceSend;
    }

    /**
     * @param float $unsubscribeCountSinceSend
     * @return SubscribersList
     */
    public function setUnsubscribeCountSinceSend($unsubscribeCountSinceSend)
    {
        $this->unsubscribeCountSinceSend = $unsubscribeCountSinceSend;
        return $this;
    }

    /**
     * @return float
     */
    public function getCleanedCountSinceSend()
    {
        return $this->cleanedCountSinceSend;
    }

    /**
     * @param float $cleanedCountSinceSend
     * @return SubscribersList
     */
    public function setCleanedCountSinceSend($cleanedCountSinceSend)
    {
        $this->cleanedCountSinceSend = $cleanedCountSinceSend;
        return $this;
    }

    /**
     * @return float
     */
    public function getCampaignCount()
    {
        return $this->campaignCount;
    }

    /**
     * @param float $campaignCount
     * @return SubscribersList
     */
    public function setCampaignCount($campaignCount)
    {
        $this->campaignCount = $campaignCount;
        return $this;
    }

    /**
     * @return float
     */
    public function getGroupingCount()
    {
        return $this->groupingCount;
    }

    /**
     * @param float $groupingCount
     * @return SubscribersList
     */
    public function setGroupingCount($groupingCount)
    {
        $this->groupingCount = $groupingCount;
        return $this;
    }

    /**
     * @return float
     */
    public function getGroupCount()
    {
        return $this->groupCount;
    }

    /**
     * @param float $groupCount
     * @return SubscribersList
     */
    public function setGroupCount($groupCount)
    {
        $this->groupCount = $groupCount;
        return $this;
    }

    /**
     * @return float
     */
    public function getMergeVarCount()
    {
        return $this->mergeVarCount;
    }

    /**
     * @param float $mergeVarCount
     * @return SubscribersList
     */
    public function setMergeVarCount($mergeVarCount)
    {
        $this->mergeVarCount = $mergeVarCount;
        return $this;
    }

    /**
     * @return float
     */
    public function getAvgSubRate()
    {
        return $this->avgSubRate;
    }

    /**
     * @param float $avgSubRate
     * @return SubscribersList
     */
    public function setAvgSubRate($avgSubRate)
    {
        $this->avgSubRate = $avgSubRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getAvgUsubRate()
    {
        return $this->avgUsubRate;
    }

    /**
     * @param float $avgUsubRate
     * @return SubscribersList
     */
    public function setAvgUsubRate($avgUsubRate)
    {
        $this->avgUsubRate = $avgUsubRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getTargetSubRate()
    {
        return $this->targetSubRate;
    }

    /**
     * @param float $targetSubRate
     * @return SubscribersList
     */
    public function setTargetSubRate($targetSubRate)
    {
        $this->targetSubRate = $targetSubRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getOpenRate()
    {
        return $this->openRate;
    }

    /**
     * @param float $openRate
     * @return SubscribersList
     */
    public function setOpenRate($openRate)
    {
        $this->openRate = $openRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getClickRate()
    {
        return $this->clickRate;
    }

    /**
     * @param float $clickRate
     * @return SubscribersList
     */
    public function setClickRate($clickRate)
    {
        $this->clickRate = $clickRate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return SubscribersList
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return SubscribersList
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        }

        if (!$this->updatedAt) {
            $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
