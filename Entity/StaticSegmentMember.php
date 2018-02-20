<?php

namespace Oro\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *      name="orocrm_mc_static_segment_mmbr",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="mc_segment_sid_mid_unq", columns={"static_segment_id", "member_id"})
 *      },
 *      indexes={
 *          @ORM\Index(name="mc_segment_mmbr_sid_st", columns={"static_segment_id", "state"})
 *      },
 * )
 * @Config()
 */
class StaticSegmentMember
{
    /**
     * @const For members which should be added to static segment.
     */
    const STATE_ADD = 'add';

    /**
     * @const For members which should be removed.
     */
    const STATE_REMOVE = 'remove';

    /**
     * @const For members which are already synced.
     */
    const STATE_SYNCED = 'synced';

    /**
     * @const For members which are already dropped.
     */
    const STATE_DROP = 'drop';

    /**
     * @const For members which should be dropped.
     */
    const STATE_TO_DROP = 'to_drop';

    /**
     * @const For members which should be unsubscribed.
     */
    const STATE_UNSUBSCRIBE = 'unsubscribe';

    /**
     * @const For members which should be unsubscribed and removed from static segment.
     */
    const STATE_UNSUBSCRIBE_DELETE = 'unsubscribe_delete';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\MailChimpBundle\Entity\StaticSegment", inversedBy="segmentMembers")
     * @ORM\JoinColumn(name="static_segment_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $staticSegment;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\MailChimpBundle\Entity\Member", inversedBy="segmentMembers")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $member;

    /**
     * @var string
     * @ORM\Column(name="state", type="string", length=255, nullable=false)
     */
    protected $state = self::STATE_ADD;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return StaticSegmentMember
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set staticSegment
     *
     * @param StaticSegment $staticSegment
     * @return StaticSegmentMember
     */
    public function setStaticSegment(StaticSegment $staticSegment)
    {
        $this->staticSegment = $staticSegment;

        return $this;
    }

    /**
     * Get staticSegment
     *
     * @return StaticSegment
     */
    public function getStaticSegment()
    {
        return $this->staticSegment;
    }

    /**
     * Set member
     *
     * @param Member $member
     * @return StaticSegmentMember
     */
    public function setMember(Member $member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }
}
