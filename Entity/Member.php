<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @link http://apidocs.mailchimp.com/api/2.0/lists/member-info.php
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_mailchimp_member",
 *      indexes={
 *          @ORM\Index(name="mc_mmbr_email_list_idx", columns={"email", "subscribers_list_id"}),
 *          @ORM\Index(name="mc_mmbr_origin_idx", columns={"origin_id"}),
 *          @ORM\Index(name="mc_mmbr_status_idx", columns={"status"}),
 *      },
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-user"
 *      },
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      },
 *      "form"={
 *          "grid_name"="orocrm-mailchimp-member-grid",
 *      },
 *  }
 * )
 */
class Member implements OriginAwareInterface, FirstNameInterface, LastNameInterface
{
    /**#@+
     * @const string Status of member
     */
    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUS_CLEANED = 'cleaned';
    const STATUS_EXPORT = 'export';
    const STATUS_EXPORT_FAILED = 'export_failed';
    /**#@-*/

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Mapped to field "leid": The Member id used in our web app, allows you to create a link directly to it
     *
     * @var integer
     *
     * @ORM\Column(name="origin_id", type="bigint", nullable=true)
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
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    protected $phone;

    /**
     * The subscription status for this email address, either pending, subscribed, unsubscribed, or cleaned
     *
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16, nullable=false)
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * The rating of the subscriber. This will be 1 - 5
     *
     * @var integer
     *
     * @ORM\Column(name="member_rating", type="smallint", nullable=true)
     */
    protected $memberRating;

    /**
     * The date+time the opt-in completed.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="optedin_at", type="datetime", nullable=true)
     */
    protected $optedInAt;

    /**
     * IP Address this address opted in from.
     *
     * @var string
     *
     * @ORM\Column(name="optedin_ip", type="string", length=20, nullable=true)
     */
    protected $optedInIpAddress;

    /**
     * The date+time the confirm completed.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="confirmed_at", type="datetime", nullable=true)
     */
    protected $confirmedAt;

    /**
     * IP Address this address confirmed from.
     *
     * @var string
     *
     * @ORM\Column(name="confirmed_ip", type="string", length=16, nullable=true)
     */
    protected $confirmedIpAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=64, nullable=true)
     */
    protected $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=64, nullable=true)
     */
    protected $longitude;

    /**
     * GMT offset
     *
     * @var string
     *
     * @ORM\Column(name="gmt_offset", type="string", length=16, nullable=true)
     */
    protected $gmtOffset;

    /**
     * GMT offset during daylight savings (if DST not observered, will be same as gmtoff)
     *
     * @var string
     *
     * @ORM\Column(name="dst_offset", type="string", length=16, nullable=true)
     */
    protected $dstOffset;

    /**
     * The timezone we've place them in
     *
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=40, nullable=true)
     */
    protected $timezone;

    /**
     * 2 digit ISO-3166 country code
     *
     * @var string
     *
     * @ORM\Column(name="cc", type="string", length=2, nullable=true)
     */
    protected $cc;

    /**
     * Generally state, province, or similar
     *
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     */
    protected $region;

    /**
     * The last time this record was changed. If the record is old enough, this may be blank.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="last_changed_at", type="datetime", nullable=true)
     */
    protected $lastChangedAt;

    /**
     * The unique id for an email address (not list related) - the email "id" returned from listMemberInfo,
     * Webhooks, Campaigns, etc.
     *
     * @var string
     *
     * @ORM\Column(name="euid", type="string", length=255, nullable=true)
     */
    protected $euid;

    /**
     * @var array
     *
     * @ORM\Column(name="merge_var_values", type="json_array", nullable=true)
     */
    protected $mergeVarValues;

    /**
     * @var SubscribersList
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList")
     * @ORM\JoinColumn(name="subscribers_list_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $subscribersList;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Collection|ArrayCollection|Member[]
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember", mappedBy="member")
     */
    protected $segmentMembers;

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
     * Constructor
     */
    public function __construct()
    {
        $this->segments = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getOriginId()
    {
        return $this->originId;
    }

    /**
     * @param integer $originId
     * @return Member
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
     * @return Member
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return string
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @param string $cc
     * @return Member
     */
    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    /**
     * @param \DateTime $confirmedAt
     * @return Member
     */
    public function setConfirmedAt(\DateTime $confirmedAt = null)
    {
        $this->confirmedAt = $confirmedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfirmedIpAddress()
    {
        return $this->confirmedIpAddress;
    }

    /**
     * @param string $confirmedIpAddress
     * @return Member
     */
    public function setConfirmedIpAddress($confirmedIpAddress)
    {
        $this->confirmedIpAddress = $confirmedIpAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getDstOffset()
    {
        return $this->dstOffset;
    }

    /**
     * @param string $dstOffset
     * @return Member
     */
    public function setDstOffset($dstOffset)
    {
        $this->dstOffset = $dstOffset;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Member
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return Member
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getEuid()
    {
        return $this->euid;
    }

    /**
     * @param string $euid
     * @return Member
     */
    public function setEuid($euid)
    {
        $this->euid = $euid;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return Member
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getGmtOffset()
    {
        return $this->gmtOffset;
    }

    /**
     * @param string $gmtOffset
     * @return Member
     */
    public function setGmtOffset($gmtOffset)
    {
        $this->gmtOffset = $gmtOffset;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastChangedAt()
    {
        return $this->lastChangedAt;
    }

    /**
     * @param \DateTime $lastChangedAt
     * @return Member
     */
    public function setLastChangedAt(\DateTime $lastChangedAt = null)
    {
        $this->lastChangedAt = $lastChangedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return Member
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     * @return Member
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return int
     */
    public function getLeid()
    {
        return $this->getOriginId();
    }

    /**
     * @param int $leid
     * @return Member
     */
    public function setLeid($leid)
    {
        $this->setOriginId($leid);

        return $this;
    }

    /**
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     * @return Member
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return int
     */
    public function getMemberRating()
    {
        return $this->memberRating;
    }

    /**
     * @param int $memberRating
     * @return Member
     */
    public function setMemberRating($memberRating)
    {
        $this->memberRating = $memberRating;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOptedInAt()
    {
        return $this->optedInAt;
    }

    /**
     * @param \DateTime $optedInAt
     * @return Member
     */
    public function setOptedInAt(\DateTime $optedInAt = null)
    {
        $this->optedInAt = $optedInAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptedInIpAddress()
    {
        return $this->optedInIpAddress;
    }

    /**
     * @param string $optedInIpAddress
     * @return Member
     */
    public function setOptedInIpAddress($optedInIpAddress)
    {
        $this->optedInIpAddress = $optedInIpAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string $region
     * @return Member
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Member
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return Member
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return array
     */
    public function getMergeVarValues()
    {
        return $this->mergeVarValues;
    }

    /**
     * @param array|null $data
     * @return SubscribersList
     */
    public function setMergeVarValues(array $data = null)
    {
        $this->mergeVarValues = $data;

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
     * @return Member
     */
    public function setSubscribersList(SubscribersList $subscribersList = null)
    {
        $this->subscribersList = $subscribersList;

        return $this;
    }

    /**
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Organization $owner
     * @return Member
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

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
     * @return Member
     */
    public function setCreatedAt(\DateTime $createdAt)
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
     * @return Member
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
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

    /**
     * Add segmentMembers
     *
     * @param StaticSegmentMember $segmentMembers
     * @return Member
     */
    public function addSegmentMember(StaticSegmentMember $segmentMembers)
    {
        if (!$this->segmentMembers->contains($segmentMembers)) {
            $this->segmentMembers->add($segmentMembers);
        }

        return $this;
    }

    /**
     * Remove segmentMembers
     *
     * @param StaticSegmentMember $segmentMembers
     */
    public function removeSegmentMember(StaticSegmentMember $segmentMembers)
    {
        if ($this->segmentMembers->contains($segmentMembers)) {
            $this->segmentMembers->removeElement($segmentMembers);
        }
    }

    /**
     * Get segmentMembers
     *
     * @return Collection
     */
    public function getSegmentMembers()
    {
        return $this->segmentMembers;
    }
}
