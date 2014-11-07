<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository")
 * @ORM\Table(name="orocrm_mc_static_segment")
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
 *      }
 *  }
 * )
 */
class StaticSegment implements OriginAwareInterface
{
    const STATUS_NOT_SYNCED = 'not_synced';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SYNCED = 'synced';
    const STATUS_SCHEDULED = 'scheduled';

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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
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
     * @var MarketingList
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MarketingListBundle\Entity\MarketingList")
     * @ORM\JoinColumn(name="marketing_list_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $marketingList;

    /**
     * @var SubscribersList
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList")
     * @ORM\JoinColumn(name="subscribers_list_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $subscribersList;

    /**
     * @var Collection|ArrayCollection|Member[]
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember", mappedBy="staticSegment")
     */
    protected $segmentMembers;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var string
     * @ORM\Column(name="sync_status", type="string", length=255, nullable=false)
     */
    protected $syncStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_synced", type="datetime", nullable=true)
     */
    protected $lastSynced;

    /**
     * @var bool
     *
     * @ORM\Column(name="remote_remove", type="boolean", nullable=false)
     */
    protected $remoteRemove = false;

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
     * @var \DateTime
     *
     * @ORM\Column(name="last_reset", type="datetime", nullable=true)
     */
    protected $lastReset;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="member_count", type="integer", nullable=true)
     */
    protected $memberCount;

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
     * Set name
     *
     * @param string $name
     * @return StaticSegment
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set syncStatus
     *
     * @param integer $syncStatus
     * @return StaticSegment
     */
    public function setSyncStatus($syncStatus)
    {
        $this->syncStatus = $syncStatus;

        return $this;
    }

    /**
     * Get syncStatus
     *
     * @return integer
     */
    public function getSyncStatus()
    {
        return $this->syncStatus;
    }

    /**
     * Set lastSynced
     *
     * @param \DateTime $lastSynced
     * @return StaticSegment
     */
    public function setLastSynced($lastSynced)
    {
        $this->lastSynced = $lastSynced;

        return $this;
    }

    /**
     * Get lastSynced
     *
     * @return \DateTime
     */
    public function getLastSynced()
    {
        return $this->lastSynced;
    }

    /**
     * Set remoteRemove
     *
     * @param boolean $remoteRemove
     * @return StaticSegment
     */
    public function setRemoteRemove($remoteRemove)
    {
        $this->remoteRemove = $remoteRemove;

        return $this;
    }

    /**
     * Get remoteRemove
     *
     * @return boolean
     */
    public function getRemoteRemove()
    {
        return $this->remoteRemove;
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
     * @return StaticSegment
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
     * @return StaticSegment
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Set marketingList
     *
     * @param MarketingList $marketingList
     * @return StaticSegment
     */
    public function setMarketingList(MarketingList $marketingList = null)
    {
        $this->marketingList = $marketingList;

        return $this;
    }

    /**
     * Get marketingList
     *
     * @return MarketingList
     */
    public function getMarketingList()
    {
        return $this->marketingList;
    }

    /**
     * Set subscribersList
     *
     * @param SubscribersList $subscribersList
     * @return StaticSegment
     */
    public function setSubscribersList(SubscribersList $subscribersList = null)
    {
        $this->subscribersList = $subscribersList;

        return $this;
    }

    /**
     * Get subscribersList
     *
     * @return SubscribersList
     */
    public function getSubscribersList()
    {
        return $this->subscribersList;
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
     * @return StaticSegment
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
     * @return StaticSegment
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
     * @return StaticSegment
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
     * @return \DateTime
     */
    public function getLastReset()
    {
        return $this->lastReset;
    }

    /**
     * @param \DateTime $lastReset
     *
     * @return StaticSegment
     */
    public function setLastReset(\DateTime $lastReset = null)
    {
        $this->lastReset = $lastReset;

        return $this;
    }

    /**
     * Set memberCount
     *
     * @param integer $memberCount
     * @return StaticSegment
     */
    public function setMemberCount($memberCount)
    {
        $this->memberCount = $memberCount;

        return $this;
    }

    /**
     * Get memberCount
     *
     * @return integer 
     */
    public function getMemberCount()
    {
        return $this->memberCount;
    }

    /**
     * Add segmentMembers
     *
     * @param StaticSegmentMember $segmentMembers
     * @return StaticSegment
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
