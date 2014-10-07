<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @ORM\Entity()
 * @ORM\Table(
 *      name="orocrm_mailchimp_campaign",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="mc_campaign_oid_cid_unq", columns={"origin_id", "channel_id"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-envelope"
 *      }
 *  }
 * )
 */
class Campaign
{
    const STATUS_SAVE = 'save';
    const STATUS_SENT = 'sent';
    const STATUS_SENDING = 'sending';
    const STATUS_PAUSED = 'paused';
    const STATUS_SCHEDULE = 'schedule';

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
     * @var int
     * @ORM\Column(name="web_id", type="bigint", nullable=false)
     */
    protected $webId;

    /**
     * @var EmailCampaign
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\Template")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $template;

    /**
     * @var SubscribersList
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList")
     * @ORM\JoinColumn(name="subscribers_list_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $subscribersList;

    /**
     * @var EmailCampaign
     *
     * @ORM\OneToOne(targetEntity="OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign", cascade={"persist"})
     * @ORM\JoinColumn(name="email_campaign_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $emailCampaign;

    /**
     * @var string
     *
     * @ORM\Column(name="content_type", type="string", length=50, nullable=true)
     */
    protected $contentType;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50, nullable=true)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="archive_url", type="string", length=255, nullable=true)
     */
    protected $archiveUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="archive_url_long", type="text", nullable=true)
     */
    protected $archiveUrlLong;

    /**
     * @var int
     *
     * @ORM\Column(name="emails_sent", type="integer", nullable=true)
     */
    protected $emailsSent;

    /**
     * @var int
     *
     * @ORM\Column(name="tests_sent", type="integer", nullable=true)
     */
    protected $testsSent;

    /**
     * @var int
     *
     * @ORM\Column(name="tests_remain", type="integer", nullable=true)
     */
    protected $testsRemain;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="from_name", type="string", length=255, nullable=true)
     */
    protected $fromName;

    /**
     * @var string
     *
     * @ORM\Column(name="from_email", type="string", length=255, nullable=true)
     */
    protected $fromEmail;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="send_time", type="datetime", nullable=true)
     */
    protected $sendTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_open_date", type="datetime", nullable=true)
     */
    protected $lastOpenDate;

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
     * @var int
     *
     * @ORM\Column(name="syntax_errors", type="integer", nullable=true)
     */
    protected $syntaxErrors;

    /**
     * @var int
     *
     * @ORM\Column(name="hard_bounces", type="integer", nullable=true)
     */
    protected $hardBounces;

    /**
     * @var int
     *
     * @ORM\Column(name="soft_bounces", type="integer", nullable=true)
     */
    protected $softBounces;

    /**
     * @var int
     *
     * @ORM\Column(name="unsubscribes", type="integer", nullable=true)
     */
    protected $unsubscribes;

    /**
     * @var int
     *
     * @ORM\Column(name="abuse_reports", type="integer", nullable=true)
     */
    protected $abuseReports;

    /**
     * @var int
     *
     * @ORM\Column(name="forwards", type="integer", nullable=true)
     */
    protected $forwards;

    /**
     * @var int
     *
     * @ORM\Column(name="forwards_opens", type="integer", nullable=true)
     */
    protected $forwardsOpens;

    /**
     * @var int
     *
     * @ORM\Column(name="opens", type="integer", nullable=true)
     */
    protected $opens;

    /**
     * @var int
     *
     * @ORM\Column(name="unique_opens", type="integer", nullable=true)
     */
    protected $uniqueOpens;

    /**
     * @var int
     *
     * @ORM\Column(name="clicks", type="integer", nullable=true)
     */
    protected $clicks;

    /**
     * @var int
     *
     * @ORM\Column(name="unique_clicks", type="integer", nullable=true)
     */
    protected $uniqueClicks;

    /**
     * @var int
     *
     * @ORM\Column(name="users_who_clicked", type="integer", nullable=true)
     */
    protected $usersWhoClicked;

    /**
     * @var int
     *
     * @ORM\Column(name="unique_likes", type="integer", nullable=true)
     */
    protected $uniqueLikes;

    /**
     * @var int
     *
     * @ORM\Column(name="recipient_likes", type="integer", nullable=true)
     */
    protected $recipientLikes;

    /**
     * @var int
     *
     * @ORM\Column(name="facebook_likes", type="integer", nullable=true)
     */
    protected $facebookLikes;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Channel $integration
     * @return Campaign
     */
    public function setChannel(Channel $integration)
    {
        $this->channel = $integration;

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
     * @return EmailCampaign
     */
    public function getEmailCampaign()
    {
        return $this->emailCampaign;
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @return Campaign
     */
    public function setEmailCampaign(EmailCampaign $emailCampaign)
    {
        $this->emailCampaign = $emailCampaign;

        return $this;
    }

    /**
     * @return int
     */
    public function getAbuseReports()
    {
        return $this->abuseReports;
    }

    /**
     * @param int $abuseReports
     * @return Campaign
     */
    public function setAbuseReports($abuseReports)
    {
        $this->abuseReports = $abuseReports;
        return $this;
    }

    /**
     * @return string
     */
    public function getArchiveUrl()
    {
        return $this->archiveUrl;
    }

    /**
     * @param string $archiveUrl
     * @return Campaign
     */
    public function setArchiveUrl($archiveUrl)
    {
        $this->archiveUrl = $archiveUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getArchiveUrlLong()
    {
        return $this->archiveUrlLong;
    }

    /**
     * @param string $archiveUrlLong
     * @return Campaign
     */
    public function setArchiveUrlLong($archiveUrlLong)
    {
        $this->archiveUrlLong = $archiveUrlLong;
        return $this;
    }

    /**
     * @return int
     */
    public function getClicks()
    {
        return $this->clicks;
    }

    /**
     * @param int $clicks
     * @return Campaign
     */
    public function setClicks($clicks)
    {
        $this->clicks = $clicks;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     * @return Campaign
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
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
     * @return Campaign
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getEmailsSent()
    {
        return $this->emailsSent;
    }

    /**
     * @param int $emailsSent
     * @return Campaign
     */
    public function setEmailsSent($emailsSent)
    {
        $this->emailsSent = $emailsSent;
        return $this;
    }

    /**
     * @return int
     */
    public function getFacebookLikes()
    {
        return $this->facebookLikes;
    }

    /**
     * @param int $facebookLikes
     * @return Campaign
     */
    public function setFacebookLikes($facebookLikes)
    {
        $this->facebookLikes = $facebookLikes;
        return $this;
    }

    /**
     * @return int
     */
    public function getForwards()
    {
        return $this->forwards;
    }

    /**
     * @param int $forwards
     * @return Campaign
     */
    public function setForwards($forwards)
    {
        $this->forwards = $forwards;
        return $this;
    }

    /**
     * @return int
     */
    public function getForwardsOpens()
    {
        return $this->forwardsOpens;
    }

    /**
     * @param int $forwardsOpens
     * @return Campaign
     */
    public function setForwardsOpens($forwardsOpens)
    {
        $this->forwardsOpens = $forwardsOpens;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * @param string $fromEmail
     * @return Campaign
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     * @return Campaign
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @return int
     */
    public function getHardBounces()
    {
        return $this->hardBounces;
    }

    /**
     * @param int $hardBounces
     * @return Campaign
     */
    public function setHardBounces($hardBounces)
    {
        $this->hardBounces = $hardBounces;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastOpenDate()
    {
        return $this->lastOpenDate;
    }

    /**
     * @param \DateTime $lastOpenDate
     * @return Campaign
     */
    public function setLastOpenDate($lastOpenDate)
    {
        $this->lastOpenDate = $lastOpenDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getOpens()
    {
        return $this->opens;
    }

    /**
     * @param int $opens
     * @return Campaign
     */
    public function setOpens($opens)
    {
        $this->opens = $opens;
        return $this;
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
     * @return Campaign
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;
        return $this;
    }

    /**
     * @return int
     */
    public function getRecipientLikes()
    {
        return $this->recipientLikes;
    }

    /**
     * @param int $recipientLikes
     * @return Campaign
     */
    public function setRecipientLikes($recipientLikes)
    {
        $this->recipientLikes = $recipientLikes;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSendTime()
    {
        return $this->sendTime;
    }

    /**
     * @param \DateTime $sendTime
     * @return Campaign
     */
    public function setSendTime($sendTime)
    {
        $this->sendTime = $sendTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getSoftBounces()
    {
        return $this->softBounces;
    }

    /**
     * @param int $softBounces
     * @return Campaign
     */
    public function setSoftBounces($softBounces)
    {
        $this->softBounces = $softBounces;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Campaign
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return SubscribersList
     */
    public function getSubscribersList()
    {
        return $this->subscribersList;
    }

    /**
     * @param SubscribersList $subscribersList
     * @return Campaign
     */
    public function setSubscribersList($subscribersList)
    {
        $this->subscribersList = $subscribersList;
        return $this;
    }

    /**
     * @return int
     */
    public function getSyntaxErrors()
    {
        return $this->syntaxErrors;
    }

    /**
     * @param int $syntaxErrors
     * @return Campaign
     */
    public function setSyntaxErrors($syntaxErrors)
    {
        $this->syntaxErrors = $syntaxErrors;
        return $this;
    }

    /**
     * @return EmailCampaign
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param EmailCampaign $template
     * @return Campaign
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return int
     */
    public function getTestsRemain()
    {
        return $this->testsRemain;
    }

    /**
     * @param int $testsRemain
     * @return Campaign
     */
    public function setTestsRemain($testsRemain)
    {
        $this->testsRemain = $testsRemain;
        return $this;
    }

    /**
     * @return int
     */
    public function getTestsSent()
    {
        return $this->testsSent;
    }

    /**
     * @param int $testsSent
     * @return Campaign
     */
    public function setTestsSent($testsSent)
    {
        $this->testsSent = $testsSent;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Campaign
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Campaign
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getUniqueClicks()
    {
        return $this->uniqueClicks;
    }

    /**
     * @param int $uniqueClicks
     * @return Campaign
     */
    public function setUniqueClicks($uniqueClicks)
    {
        $this->uniqueClicks = $uniqueClicks;
        return $this;
    }

    /**
     * @return int
     */
    public function getUniqueLikes()
    {
        return $this->uniqueLikes;
    }

    /**
     * @param int $uniqueLikes
     * @return Campaign
     */
    public function setUniqueLikes($uniqueLikes)
    {
        $this->uniqueLikes = $uniqueLikes;
        return $this;
    }

    /**
     * @return int
     */
    public function getUniqueOpens()
    {
        return $this->uniqueOpens;
    }

    /**
     * @param int $uniqueOpens
     * @return Campaign
     */
    public function setUniqueOpens($uniqueOpens)
    {
        $this->uniqueOpens = $uniqueOpens;
        return $this;
    }

    /**
     * @return int
     */
    public function getUnsubscribes()
    {
        return $this->unsubscribes;
    }

    /**
     * @param int $unsubscribes
     * @return Campaign
     */
    public function setUnsubscribes($unsubscribes)
    {
        $this->unsubscribes = $unsubscribes;
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
     * @return Campaign
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getUsersWhoClicked()
    {
        return $this->usersWhoClicked;
    }

    /**
     * @param int $usersWhoClicked
     * @return Campaign
     */
    public function setUsersWhoClicked($usersWhoClicked)
    {
        $this->usersWhoClicked = $usersWhoClicked;
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
     * @return Campaign
     */
    public function setWebId($webId)
    {
        $this->webId = $webId;
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
