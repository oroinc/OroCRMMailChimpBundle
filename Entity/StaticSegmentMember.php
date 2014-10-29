<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *      name="orocrm_mailchimp_static_segment_member",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="mc_segment_sid_mid_unq", columns={"static_segment_id", "member_id"})
 *     }
 * )
 * @Config()
 */
class StaticSegmentMember
{
    const STATE_ADD = 'add';
    const STATE_REMOVE = 'remove';
    const STATE_SYNCED = 'synced';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment", inversedBy="segmentMembers")
     * @ORM\JoinColumn(name="static_segment_id", referencedColumnName="id", nullable=false)
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
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\Member", inversedBy="segmentMembers")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", nullable=false)
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
