<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_mc_extended_merge_var",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="mc_extended_merge_var_sid_name_unq", columns={"static_segment_id", "name"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config()
 */
class ExtendedMergeVar
{
    const STATE_ADD = 'add';
    const STATE_REMOVE = 'remove';
    const STATE_SYNCED = 'synced';
    const STATE_DROPPED = 'dropped';

    const NAME_PREFIX = 'e_';

    const TAG_FIELD_TYPE = 'text';

    const TAG_PREFIX = 'E_';
    const MAXIMUM_TAG_LENGTH = 10;

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
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $label;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_require", type="boolean", options={"default"=false})
     */
    protected $require;

    /**
     * @var string
     *
     * @ORM\Column(name="field_type", type="string", length=255, nullable=false, options={"default"="text"})
     */
    protected $fieldType;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=false)
     */
    protected $tag;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=false)
     */
    protected $state;

    public function __construct()
    {
        $this->require = false;
        $this->fieldType = self::TAG_FIELD_TYPE;
        $this->state = self::STATE_ADD;
    }

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
     * @return ExtendedMergeVar
     */
    public function setStaticSegment(StaticSegment $staticSegment)
    {
        $this->staticSegment = $staticSegment;
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
     * @return string
     */
    public function getNameWithPrefix()
    {
        return self::NAME_PREFIX . $this->name;
    }

    /**
     * @param string $name
     * @return ExtendedMergeVar
     */
    public function setName($name)
    {
        if (false === is_string($name) || empty($name)) {
            throw new \InvalidArgumentException('Name must be not empty string.');
        }
        if ($name !== $this->name) {
            $this->generateTag($name);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return ExtendedMergeVar
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRequire()
    {
        return $this->require;
    }

    /**
     * @return string
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
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
     * Check if in the add state
     *
     * @return bool
     */
    public function isAddState()
    {
        return self::STATE_ADD == $this->state;
    }

    /**
     * @return bool
     */
    public function isRemoveState()
    {
        return self::STATE_REMOVE == $this->state;
    }

    /**
     * @return void
     */
    public function setSyncedState()
    {
        $this->state = self::STATE_SYNCED;
    }

    /**
     * @return void
     */
    public function setDroppedState()
    {
        $this->state = self::STATE_DROPPED;
    }

    /**
     * @param string $name
     * @return void
     */
    protected function generateTag($name)
    {
        $tag = self::TAG_PREFIX . strtoupper($name);
        if (strlen($tag) > self::MAXIMUM_TAG_LENGTH) {
            $tag = preg_replace('#[aeiou\s]+#i', '', $name);
            $tag = self::TAG_PREFIX . strtoupper($tag);
            if (strlen($tag) > self::MAXIMUM_TAG_LENGTH) {
                $tag = substr($tag, 0, self::MAXIMUM_TAG_LENGTH);
            }
        }
        $this->tag = $tag;
    }
}
