<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_mailchimp_template",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="mc_template_oid_cid_unq", columns={"origin_id", "channel_id"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-file-alt"
 *      }
 *  }
 * )
 */
class Template
{
    use IntegrationEntityTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(name="origin_id", type="bigint", nullable=false)
     */
    protected $originId;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var bool
     * @ORM\Column(name="is_active", type="bool")
     */
    protected $active;

    /**
     * @var string
     * @ORM\Column(name="layout", type="text", nullable=true)
     */
    protected $layout;

    /**
     * @var string
     * @ORM\Column(name="category", type="string", length=255, nullable=true)
     */
    protected $category;

    /**
     * @var string
     * @ORM\Column(name="preview_image", type="text", nullable=true)
     */
    protected $previewImage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
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
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return Template
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return Template
     */
    public function setCategory($category)
    {
        $this->category = $category;

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
     * @return Template
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     * @return Template
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

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
     * @return Template
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return Template
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginId()
    {
        return $this->originId;
    }

    /**
     * @param int $originId
     * @return Template
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPreviewImage()
    {
        return $this->previewImage;
    }

    /**
     * @param string $previewImage
     * @return Template
     */
    public function setPreviewImage($previewImage)
    {
        $this->previewImage = $previewImage;

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

        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
