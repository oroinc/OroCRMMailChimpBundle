<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_mc_mmbr_extd_merge_var",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="mc_mmbr_extd_merge_var_sid_mmbr_unq",
 *          columns={"static_segment_id", "member_id"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config()
 */
class MemberExtendedMergeVar
{
    const STATE_ADD = 'add';
    const STATE_REMOVE = 'remove';
    const STATE_SYNCED = 'synced';
    const STATE_DROPPED = 'dropped';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var StaticSegment
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment", inversedBy="extendedMergeVars")
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
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\Member")
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
     * @var array
     *
     * @ORM\Column(name="merge_var_values", type="json_array", nullable=true)
     */
    protected $mergeVarValues;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=false)
     */
    protected $state;

    /**
     * @var array
     */
    protected $mergeVarValuesContext;

    public function __construct()
    {
        $this->state = self::STATE_ADD;
        $this->mergeVarValues = [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return MemberExtendedMergeVar
     */
    public function setStaticSegment(StaticSegment $staticSegment)
    {
        $this->staticSegment = $staticSegment;
        return $this;
    }

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     * @return MemberExtendedMergeVar
     */
    public function setMember(Member $member)
    {
        $this->member = $member;
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
     * @param array $mergeVarValues
     */
    public function setMergeVarValues(array $mergeVarValues)
    {
        $this->mergeVarValues = $mergeVarValues;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return void
     */
    public function setSyncedState()
    {
        $this->state = self::STATE_SYNCED;
    }

    /**
     * @return bool
     */
    public function isAddState()
    {
        return $this->state === self::STATE_ADD;
    }

    /**
     * @param array $context
     * @return MemberExtendedMergeVar
     */
    public function setMergeVarValuesContext(array $context)
    {
        $this->mergeVarValuesContext = $context;
        return $this;
    }

    /**
     * @return array
     */
    public function getMergeVarValuesContext()
    {
        return $this->mergeVarValuesContext;
    }
}
