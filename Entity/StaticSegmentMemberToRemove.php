<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_mc_tmp_mmbr_to_remove"
 * )
 */
class StaticSegmentMemberToRemove
{
    /**
     * @var Member
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $member;

    /**
     * @var StaticSegment
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment")
     * @ORM\JoinColumn(name="static_segment_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $staticSegment;

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     * @return StaticSegmentMemberToRemove
     */
    public function setMember(Member $member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return StaticSegment
     */
    public function getStaticSegment()
    {
        return $this->staticSegment;
    }

    /**
     * @param StaticSegment $staticSegment
     * @return StaticSegmentMemberToRemove
     */
    public function setStaticSegment(StaticSegment $staticSegment)
    {
        $this->staticSegment = $staticSegment;

        return $this;
    }
}
